import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import { prisma } from "@/lib/prisma";

export async function POST(req: NextRequest) {
  try {
    const session = await auth();
    if (session?.user?.role !== "ADMIN") {
      return NextResponse.json({ error: "Không có quyền thực hiện" }, { status: 403 });
    }

    const { depositId } = await req.json();
    if (!depositId) {
      return NextResponse.json({ error: "Thiếu mã đơn nạp tiền" }, { status: 400 });
    }

    const deposit = await prisma.deposit.findUnique({
      where: { id: depositId },
      include: { user: { select: { name: true, email: true } } },
    });

    if (!deposit) {
      return NextResponse.json({ error: "Không tìm thấy đơn nạp tiền" }, { status: 404 });
    }

    if (deposit.status === "SUCCESS") {
      return NextResponse.json({ error: "Đơn nạp tiền này đã được duyệt thành công trước đó!" }, { status: 400 });
    }

    if (deposit.status === "CANCELLED") {
      return NextResponse.json({ error: "Đơn nạp tiền đã bị huỷ, không thể duyệt." }, { status: 400 });
    }

    const creditAmount = deposit.realValue ?? deposit.amount;

    // [SECURITY] Atomic update: chỉ cập nhật nếu status vẫn là PENDING
    // Tránh race condition khi webhook SePay và admin approve cùng lúc
    const updatedCount = await prisma.deposit.updateMany({
      where: { id: depositId, status: "PENDING" },
      data: {
        status: "SUCCESS",
        confirmedAt: new Date(),
        cardMessage: "Đã duyệt thủ công bởi Admin",
      },
    });

    if (updatedCount.count === 0) {
      // Race condition: webhook đã xử lý trước khi admin nhấn duyệt
      return NextResponse.json(
        { error: "Đơn nạp tiền đã được xử lý tự động trước đó. Vui lòng tải lại trang." },
        { status: 409 }
      );
    }

    // Cộng tiền sau khi update thành công
    await prisma.user.update({
      where: { id: deposit.userId },
      data: { balance: { increment: creditAmount } },
    });

    return NextResponse.json({
      success: true,
      message: `Đã duyệt đơn nạp tiền và cộng ${creditAmount.toLocaleString("vi-VN")}đ cho người dùng thành công!`,
    });
  } catch (err: any) {
    console.error("[POST /api/admin/deposits/approve Error]:", err);
    return NextResponse.json({ error: err.message || "Lỗi xử lý duyệt đơn nạp tiền" }, { status: 500 });
  }
}

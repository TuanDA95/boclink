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

    const creditAmount = deposit.realValue ?? deposit.amount;

    await prisma.$transaction([
      prisma.deposit.update({
        where: { id: depositId },
        data: {
          status: "SUCCESS",
          confirmedAt: new Date(),
          cardMessage: "Đã duyệt thủ công bởi Admin",
        },
      }),
      prisma.user.update({
        where: { id: deposit.userId },
        data: {
          balance: { increment: creditAmount },
        },
      }),
    ]);

    return NextResponse.json({
      success: true,
      message: `Đã duyệt đơn nạp tiền và cộng ${creditAmount.toLocaleString("vi-VN")}đ cho người dùng thành công!`,
    });
  } catch (err: any) {
    console.error("[POST /api/admin/deposits/approve Error]:", err);
    return NextResponse.json({ error: err.message || "Lỗi xử lý duyệt đơn nạp tiền" }, { status: 500 });
  }
}

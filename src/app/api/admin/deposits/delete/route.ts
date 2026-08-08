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
    });

    if (!deposit) {
      return NextResponse.json({ error: "Không tìm thấy đơn nạp tiền" }, { status: 404 });
    }

    // Chỉ cho phép xoá các đơn CANCELLED hoặc PENDING
    if (deposit.status !== "CANCELLED" && deposit.status !== "PENDING") {
      return NextResponse.json(
        { error: "Chỉ có thể xoá đơn nạp có trạng thái Đã hủy hoặc Chờ xử lý" },
        { status: 400 }
      );
    }

    await prisma.deposit.delete({ where: { id: depositId } });

    return NextResponse.json({ success: true, message: "Đã xoá đơn nạp tiền thành công!" });
  } catch (err: any) {
    console.error("[POST /api/admin/deposits/delete Error]:", err);
    return NextResponse.json({ error: err.message || "Lỗi xử lý xoá đơn nạp tiền" }, { status: 500 });
  }
}

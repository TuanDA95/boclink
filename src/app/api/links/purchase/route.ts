import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import { prisma } from "@/lib/prisma";
import { getSetting } from "@/lib/settings";
import { resolveOriginalUrl } from "@/lib/url-resolver";

export async function POST(req: NextRequest) {
  const session = await auth();
  if (!session?.user?.id) {
    return NextResponse.json({ error: "Vui lòng đăng nhập để mua link" }, { status: 401 });
  }

  const { slug } = await req.json().catch(() => ({}));
  if (!slug) return NextResponse.json({ error: "Thiếu mã link (slug)" }, { status: 400 });

  const link = await prisma.link.findUnique({
    where: { slug: slug.trim(), isActive: true },
  });
  if (!link) return NextResponse.json({ error: "Link không tồn tại hoặc đã bị ẩn" }, { status: 404 });

  const resolvedUrl = await resolveOriginalUrl(link.originalUrl);

  // Kiểm tra thời gian hết hạn mua (mặc định 12h)
  const expireHoursRaw = await getSetting("SITE_PURCHASE_EXPIRE_HOURS", "12");
  const expireHours = parseFloat(expireHoursRaw) || 12;
  const activeThreshold = new Date(Date.now() - expireHours * 60 * 60 * 1000);

  // Kiểm tra người dùng đã mua link này trong khoảng thời gian còn hiệu lực chưa
  const activePurchase = await prisma.purchase.findFirst({
    where: {
      userId: session.user.id,
      linkId: link.id,
      createdAt: { gte: activeThreshold },
    },
    orderBy: { createdAt: "desc" },
  });

  if (activePurchase) {
    return NextResponse.json({
      success: true,
      originalUrl: resolvedUrl,
      alreadyPurchased: true,
      amount: activePurchase.amount,
      message: `Bạn đã sở hữu link này (hiệu lực trong ${expireHours}h).`,
    });
  }

  // Tính giá thực tế (nếu link.price <= 0 thì dùng DEFAULT_LINK_PRICE)
  const defaultPriceRaw = await getSetting("DEFAULT_LINK_PRICE", "5000");
  const defaultLinkPrice = parseFloat(defaultPriceRaw) || 5000;
  const effectivePrice = link.price > 0 ? link.price : defaultLinkPrice;

  // Kiểm tra số dư tài khoản
  const user = await prisma.user.findUnique({ where: { id: session.user.id } });
  if (!user || user.balance < effectivePrice) {
    return NextResponse.json(
      {
        error: `Số dư không đủ. Giá link là ${effectivePrice.toLocaleString("vi-VN")}đ (Số dư hiện tại: ${(user?.balance || 0).toLocaleString("vi-VN")}đ)`,
      },
      { status: 400 }
    );
  }

  // Transaction: Trừ balance + tạo bản ghi Purchase
  const [updatedUser, newPurchase] = await prisma.$transaction([
    prisma.user.update({
      where: { id: session.user.id },
      data: { balance: { decrement: effectivePrice } },
    }),
    prisma.purchase.create({
      data: { userId: session.user.id, linkId: link.id, amount: effectivePrice },
    }),
  ]);

  return NextResponse.json({
    success: true,
    originalUrl: resolvedUrl,
    alreadyPurchased: false,
    amount: effectivePrice,
    newBalance: updatedUser.balance,
  });
}

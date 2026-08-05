import { NextRequest, NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { registerSchema } from "@/lib/validations";
import bcrypt from "bcryptjs";

export async function POST(req: NextRequest) {
  const body = await req.json().catch(() => ({}));
  const parsed = registerSchema.safeParse(body);
  if (!parsed.success) {
    return NextResponse.json({ error: parsed.error.issues[0].message }, { status: 400 });
  }

  const { account, email, password } = parsed.data;

  // Kiểm tra tài khoản đã tồn tại chưa
  const existingUser = await prisma.user.findFirst({
    where: { name: account.trim() },
  });
  if (existingUser) {
    return NextResponse.json({ error: "Tài khoản này đã được sử dụng" }, { status: 409 });
  }

  // Nếu người dùng có nhập Email thì kiểm tra trùng
  const cleanEmail = email && email.trim() !== "" ? email.trim() : null;
  if (cleanEmail) {
    const existingEmail = await prisma.user.findUnique({ where: { email: cleanEmail } });
    if (existingEmail) {
      return NextResponse.json({ error: "Email này đã được sử dụng" }, { status: 409 });
    }
  }

  const hashedPassword = await bcrypt.hash(password, 12);

  const user = await prisma.user.create({
    data: {
      name: account.trim(),
      email: cleanEmail,
      password: hashedPassword,
    },
    select: { id: true, email: true, name: true, role: true },
  });

  return NextResponse.json({ user }, { status: 201 });
}

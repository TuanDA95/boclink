import { prisma } from "@/lib/prisma";
import { auth } from "@/lib/auth";
import { redirect } from "next/navigation";
import UsersClient from "./UsersClient";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Quản lý Thành viên" };

export default async function AdminUsersPage() {
  const session = await auth();
  if (session?.user?.role !== "ADMIN") {
    redirect("/");
  }

  let users: any[] = [];
  let total = 0;

  try {
    const [fetchedUsers, fetchedTotal] = await Promise.all([
      prisma.user.findMany({
        orderBy: { createdAt: "desc" },
        take: 50,
        select: {
          id: true,
          name: true,
          email: true,
          role: true,
          balance: true,
          createdAt: true,
          _count: { select: { purchases: true, deposits: true, links: true } },
        },
      }),
      prisma.user.count(),
    ]);
    users = fetchedUsers;
    total = fetchedTotal;
  } catch (err) {
    console.error("Lỗi truy vấn Users:", err);
  }

  return <UsersClient initialUsers={JSON.parse(JSON.stringify(users))} total={total} />;
}

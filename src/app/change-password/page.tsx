import { auth } from "@/lib/auth";
import { redirect } from "next/navigation";
import ChangePasswordClient from "./ChangePasswordClient";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Đổi mật khẩu - API Key" };

export default async function ChangePasswordPage() {
  const session = await auth();
  if (!session?.user) {
    redirect("/login?callbackUrl=/change-password");
  }

  return <ChangePasswordClient username={session.user.name || session.user.email || ""} />;
}

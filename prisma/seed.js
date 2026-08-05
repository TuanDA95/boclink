const { PrismaClient } = require("@prisma/client");
const bcrypt = require("bcryptjs");

const prisma = new PrismaClient();

async function main() {
  const email = "admin@boclink.com";
  const password = "Admin@123456";
  const name = "Admin";

  const existing = await prisma.user.findUnique({ where: { email } });
  if (existing) {
    console.log("✅ Admin đã tồn tại:", email);
    // Nếu chưa phải ADMIN thì update
    if (existing.role !== "ADMIN") {
      await prisma.user.update({ where: { email }, data: { role: "ADMIN" } });
      console.log("🔄 Đã cập nhật role thành ADMIN");
    }
    return;
  }

  const hashedPassword = await bcrypt.hash(password, 12);

  const user = await prisma.user.create({
    data: {
      email,
      name,
      password: hashedPassword,
      role: "ADMIN",
    },
  });

  console.log("✅ Tạo admin thành công!");
  console.log("   Email   :", user.email);
  console.log("   Password: Admin@123456");
  console.log("   Role    :", user.role);
}

main()
  .catch((e) => {
    console.error("❌ Lỗi:", e.message);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });

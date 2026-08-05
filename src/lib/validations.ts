import { z } from "zod";

export const loginSchema = z.object({
  account: z.string().min(1, "Vui lòng nhập Email hoặc Tài khoản"),
  password: z.string().min(6, "Mật khẩu tối thiểu 6 ký tự"),
});

export const registerSchema = z
  .object({
    account: z
      .string()
      .min(3, "Tài khoản người dùng tối thiểu 3 ký tự")
      .max(30, "Tài khoản người dùng tối đa 30 ký tự")
      .regex(/^[a-zA-Z0-9_.-]+$/, "Tài khoản chỉ được chứa chữ cái, số, dấu gạch (không chứa dấu cách)"),
    email: z
      .string()
      .email("Email không hợp lệ")
      .optional()
      .or(z.literal("")),
    password: z.string().min(6, "Mật khẩu tối thiểu 6 ký tự"),
    confirmPassword: z.string(),
  })
  .refine((data) => data.password === data.confirmPassword, {
    message: "Mật khẩu xác nhận không khớp",
    path: ["confirmPassword"],
  });

export const linkSchema = z.object({
  slug: z
    .string()
    .min(1, "Mã code không được trống")
    .max(50)
    .regex(/^[a-z0-9-]+$/, "Code chỉ chứa chữ thường, số và dấu gạch ngang"),
  originalUrl: z.string().url("URL không hợp lệ"),
  title: z.string().max(200).optional(),
  description: z.string().max(500).optional(),
  price: z.coerce.number().min(0).optional().default(0),
  adDuration: z.coerce.number().int().min(5).max(300).optional().default(10),
  isActive: z.boolean().optional().default(true),
});

// Schema đơn giản cho form Quicklink (Code + URL)
export const quickLinkSchema = z.object({
  code: z
    .string()
    .min(1, "Mã code không được trống")
    .max(50)
    .regex(/^[a-z0-9-]+$/, "Code chỉ chứa chữ thường, số và dấu gạch ngang"),
  url: z.string().url("URL không hợp lệ"),
});

export const depositBankSchema = z.object({
  amount: z.coerce
    .number()
    .min(10000, "Số tiền nạp tối thiểu 10.000 VNĐ")
    .max(100000000, "Số tiền nạp tối đa 100.000.000 VNĐ"),
});

export const depositCardSchema = z.object({
  amount: z.coerce
    .number()
    .min(10000, "Số tiền nạp tối thiểu 10.000 VNĐ")
    .max(100000000, "Số tiền nạp tối đa 100.000.000 VNĐ"),
});

export type LoginInput = z.infer<typeof loginSchema>;
export type RegisterInput = z.infer<typeof registerSchema>;
export type LinkInput = z.infer<typeof linkSchema>;
export type DepositBankInput = z.infer<typeof depositBankSchema>;
export type DepositCardInput = z.infer<typeof depositCardSchema>;

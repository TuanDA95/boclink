import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  /* config options here */
  reactCompiler: true,
  allowedDevOrigins: ['trough-makeshift-glare.ngrok-free.dev'],
  experimental: {
    cpus: 1,
  },
};

export default nextConfig;

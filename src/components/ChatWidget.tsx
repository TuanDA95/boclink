"use client";

import { useEffect, useState } from "react";
import { MessageCircleMore } from "lucide-react";

interface ChatWidgetProps {
  initialChatUrl?: string;
}

export default function ChatWidget({ initialChatUrl = "" }: ChatWidgetProps) {
  const [chatUrl, setChatUrl] = useState(initialChatUrl);
  const [hovered, setHovered] = useState(false);

  useEffect(() => {
    // Luôn fetch cấu hình mới nhất từ client để đảm bảo phản hồi ngay lập tức khi Admin cập nhật
    fetch("/api/public/settings")
      .then((res) => res.json())
      .then((data) => {
        if (data && typeof data.chatLinkUrl === "string") {
          setChatUrl(data.chatLinkUrl.trim());
        }
      })
      .catch(() => {});
  }, []);

  if (!chatUrl || !chatUrl.trim()) return null;

  const handleClick = () => {
    let url = chatUrl.trim();
    if (!/^https?:\/\//i.test(url)) {
      url = "https://" + url;
    }
    window.open(url, "_blank", "noopener,noreferrer");
  };

  return (
    <>
      <style>{`
        .chat-widget-fab {
          position: fixed;
          bottom: 24px;
          right: 24px;
          z-index: 9999;
          display: flex;
          align-items: center;
          justify-content: center;
          width: 56px;
          height: 56px;
          border-radius: 50%;
          background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%);
          color: #ffffff;
          box-shadow: 0 8px 24px rgba(99, 102, 241, 0.45), 0 2px 8px rgba(0, 0, 0, 0.2);
          cursor: pointer;
          border: 1px solid rgba(255, 255, 255, 0.2);
          transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
          outline: none;
          user-select: none;
        }

        .chat-widget-fab:hover {
          transform: translateY(-4px) scale(1.08);
          box-shadow: 0 12px 32px rgba(139, 92, 246, 0.6), 0 4px 12px rgba(0, 0, 0, 0.3);
          border-color: rgba(255, 255, 255, 0.4);
        }

        .chat-widget-fab:active {
          transform: translateY(-1px) scale(0.98);
        }

        /* Online pulsing badge */
        .chat-status-dot {
          position: absolute;
          top: 2px;
          right: 2px;
          width: 14px;
          height: 14px;
          background-color: #10b981;
          border: 2px solid #090d16;
          border-radius: 50%;
        }

        .chat-status-dot::after {
          content: "";
          position: absolute;
          top: -2px;
          left: -2px;
          width: 14px;
          height: 14px;
          border-radius: 50%;
          background-color: #10b981;
          opacity: 0.75;
          animation: chatPulse 2s cubic-bezier(0, 0, 0.2, 1) infinite;
        }

        @keyframes chatPulse {
          0% {
            transform: scale(0.95);
            opacity: 0.8;
          }
          70% {
            transform: scale(2.2);
            opacity: 0;
          }
          100% {
            transform: scale(2.2);
            opacity: 0;
          }
        }

        /* Tooltip */
        .chat-tooltip {
          position: absolute;
          right: 68px;
          bottom: 10px;
          background: rgba(15, 23, 42, 0.9);
          backdrop-filter: blur(8px);
          color: #f8fafc;
          padding: 8px 14px;
          border-radius: 12px;
          font-size: 0.82rem;
          font-weight: 600;
          white-space: nowrap;
          box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
          border: 1px solid rgba(255, 255, 255, 0.1);
          pointer-events: none;
          opacity: 0;
          transform: translateX(10px) scale(0.95);
          transition: all 0.2s ease-out;
        }

        .chat-widget-fab:hover .chat-tooltip {
          opacity: 1;
          transform: translateX(0) scale(1);
        }

        .chat-tooltip::after {
          content: "";
          position: absolute;
          right: -5px;
          top: 50%;
          transform: translateY(-50%) rotate(45deg);
          width: 8px;
          height: 8px;
          background: rgba(15, 23, 42, 0.9);
          border-right: 1px solid rgba(255, 255, 255, 0.1);
          border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        @media (max-width: 640px) {
          .chat-widget-fab {
            bottom: 18px;
            right: 18px;
            width: 50px;
            height: 50px;
          }
          .chat-tooltip {
            display: none;
          }
        }
      `}</style>

      <button
        className="chat-widget-fab"
        onClick={handleClick}
        onMouseEnter={() => setHovered(true)}
        onMouseLeave={() => setHovered(false)}
        aria-label="Chat Hỗ Trợ"
        title="Chat Hỗ Trợ"
      >
        <MessageCircleMore size={28} strokeWidth={2.2} />
        <span className="chat-status-dot" />
        <div className="chat-tooltip">Chat hỗ trợ 💬</div>
      </button>
    </>
  );
}

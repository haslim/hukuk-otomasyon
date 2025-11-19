import { useState, useCallback } from 'react';

export interface Toast {
  id?: string;
  title?: string;
  description?: string;
  variant?: 'default' | 'destructive';
  duration?: number;
}

export interface ToastState {
  toasts: Toast[];
}

let toastCount = 0;

function generateId(): string {
  return `toast-${toastCount++}`;
}

export function useToast() {
  const [toasts, setToasts] = useState<Toast[]>([]);

  const toast = useCallback(({ title, description, variant = 'default', duration = 5000 }: Toast) => {
    const id = generateId();
    const newToast: Toast = { id, title, description, variant };

    setToasts((prev) => [...prev, newToast]);

    if (duration > 0) {
      setTimeout(() => {
        setToasts((prev) => prev.filter((t) => t.id !== id));
      }, duration);
    }

    return id;
  }, []);

  const dismiss = useCallback((id: string) => {
    setToasts((prev) => prev.filter((t) => t.id !== id));
  }, []);

  return {
    toast,
    dismiss,
    toasts,
  };
}

// Export a standalone toast function for global usage
export const toast = ({ title, description, variant = 'default', duration = 5000 }: Toast) => {
  // This is a simplified implementation for now
  // In a real app, this would integrate with a global toast context
  console.error('Toast called:', { title, description, variant });
  return generateId();
};

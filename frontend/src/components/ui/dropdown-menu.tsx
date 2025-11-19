import React from 'react';
import { cn } from '@/lib/utils.ts';

export interface DropdownMenuProps {
  children: React.ReactNode;
}

export const DropdownMenu = ({ children }: DropdownMenuProps) => {
  return <div className="relative inline-block text-left">{children}</div>;
};

export interface DropdownMenuTriggerProps {
  children: React.ReactNode;
  asChild?: boolean;
}

export const DropdownMenuTrigger = ({ children, asChild = false }: DropdownMenuTriggerProps) => {
  return <>{children}</>;
};

export interface DropdownMenuContentProps {
  children: React.ReactNode;
  className?: string;
  align?: 'start' | 'center' | 'end';
}

export const DropdownMenuContent = ({ children, className, align = 'end' }: DropdownMenuContentProps) => {
  const alignmentClasses = {
    start: 'left-0',
    center: 'left-1/2 transform -translate-x-1/2',
    end: 'right-0'
  };

  return (
    <div className={cn(
      `absolute z-10 mt-2 w-48 rounded-md bg-background border shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none ${alignmentClasses[align]}`,
      className
    )}>
      <div className="py-1">{children}</div>
    </div>
  );
};

export interface DropdownMenuItemProps {
  children: React.ReactNode;
  onClick?: () => void;
  className?: string;
  asChild?: boolean;
}

export const DropdownMenuItem = ({ children, onClick, className, asChild = false }: DropdownMenuItemProps) => {
  if (asChild) {
    return <>{children}</>;
  }

  return (
    <button
      onClick={onClick}
      className={cn(
        'w-full text-left px-4 py-2 text-sm hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground',
        className
      )}
    >
      {children}
    </button>
  );
};

export const DropdownMenuSeparator = () => {
  return <hr className="my-1 border-t border-border" />;
};

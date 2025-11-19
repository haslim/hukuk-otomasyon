import React from 'react';
import { cn } from '@/lib/utils.ts';

export interface SelectProps {
  value?: string;
  onValueChange?: (value: string) => void;
  children: React.ReactNode;
  className?: string;
  placeholder?: string;
}

export const Select = ({ value, onValueChange, children, className, placeholder }: SelectProps) => {
  return (
    <select
      value={value}
      onChange={(e) => onValueChange?.(e.target.value)}
      className={cn(
        'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
        className
      )}
    >
      {placeholder && (
        <option value="">{placeholder}</option>
      )}
      {children}
    </select>
  );
};

export const SelectContent = ({ children }: { children: React.ReactNode }) => {
  return <>{children}</>;
};

export const SelectItem = ({ value, children }: { value: string; children: React.ReactNode }) => {
  return <option value={value}>{children}</option>;
};

export const SelectTrigger = ({ children, className }: { children: React.ReactNode; className?: string }) => {
  return (
    <div className={cn('w-full', className)}>
      {children}
    </div>
  );
};

export const SelectValue = ({ placeholder }: { placeholder?: string }) => {
  return <option value="">{placeholder}</option>;
};

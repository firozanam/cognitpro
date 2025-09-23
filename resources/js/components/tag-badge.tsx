import React from 'react';
import { Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { X } from 'lucide-react';
import { cn } from '@/lib/utils';

interface Tag {
  id: number;
  name: string;
  slug: string;
  prompts_count?: number;
}

interface TagBadgeProps {
  tag: Tag;
  variant?: 'default' | 'secondary' | 'outline' | 'destructive';
  size?: 'sm' | 'md' | 'lg';
  interactive?: boolean;
  removable?: boolean;
  onRemove?: (tag: Tag) => void;
  showCount?: boolean;
  className?: string;
}

export function TagBadge({
  tag,
  variant = 'outline',
  size = 'md',
  interactive = false,
  removable = false,
  onRemove,
  showCount = false,
  className = ''
}: TagBadgeProps) {
  const sizeClasses = {
    sm: 'text-xs px-2 py-0.5',
    md: 'text-sm px-2.5 py-1',
    lg: 'text-base px-3 py-1.5'
  };

  const handleRemove = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    if (onRemove) {
      onRemove(tag);
    }
  };

  const badgeContent = (
    <Badge 
      variant={variant}
      className={cn(
        sizeClasses[size],
        interactive && 'hover:bg-primary hover:text-primary-foreground cursor-pointer transition-colors',
        removable && 'pr-1',
        className
      )}
    >
      <span>{tag.name}</span>
      {showCount && tag.prompts_count !== undefined && (
        <span className="ml-1 opacity-70">({tag.prompts_count})</span>
      )}
      {removable && (
        <button
          onClick={handleRemove}
          className="ml-1 hover:bg-destructive hover:text-destructive-foreground rounded-full p-0.5 transition-colors"
        >
          <X className="h-3 w-3" />
        </button>
      )}
    </Badge>
  );

  if (interactive && !removable) {
    return (
      <Link href={`/tags/${tag.slug}`}>
        {badgeContent}
      </Link>
    );
  }

  return badgeContent;
}

export default TagBadge;

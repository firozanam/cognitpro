import React, { useState } from 'react';
import { Star } from 'lucide-react';
import { cn } from '@/lib/utils';

interface RatingStarsProps {
  rating: number;
  maxRating?: number;
  size?: 'xs' | 'sm' | 'md' | 'lg';
  interactive?: boolean;
  onRatingChange?: (rating: number) => void;
  showValue?: boolean;
  className?: string;
}

export function RatingStars({
  rating,
  maxRating = 5,
  size = 'md',
  interactive = false,
  onRatingChange,
  showValue = false,
  className = ''
}: RatingStarsProps) {
  const [hoverRating, setHoverRating] = useState(0);

  const sizeClasses = {
    xs: 'h-2.5 w-2.5',
    sm: 'h-3 w-3',
    md: 'h-4 w-4',
    lg: 'h-5 w-5'
  };

  const textSizeClasses = {
    xs: 'text-xs',
    sm: 'text-xs',
    md: 'text-sm',
    lg: 'text-base'
  };

  const handleStarClick = (starRating: number) => {
    if (interactive && onRatingChange) {
      onRatingChange(starRating);
    }
  };

  const handleStarHover = (starRating: number) => {
    if (interactive) {
      setHoverRating(starRating);
    }
  };

  const handleMouseLeave = () => {
    if (interactive) {
      setHoverRating(0);
    }
  };

  const getStarFill = (starIndex: number) => {
    const currentRating = hoverRating || rating;
    
    if (starIndex <= Math.floor(currentRating)) {
      return 'fill-yellow-400 text-yellow-400';
    } else if (starIndex === Math.ceil(currentRating) && currentRating % 1 !== 0) {
      // Partial star - you could implement this with CSS gradients if needed
      return 'fill-yellow-200 text-yellow-400';
    } else {
      return 'fill-gray-200 text-gray-300';
    }
  };

  return (
    <div className={cn('flex items-center gap-1', className)}>
      <div 
        className="flex items-center gap-0.5"
        onMouseLeave={handleMouseLeave}
      >
        {Array.from({ length: maxRating }, (_, index) => {
          const starIndex = index + 1;
          return (
            <Star
              key={starIndex}
              className={cn(
                sizeClasses[size],
                getStarFill(starIndex),
                interactive && 'cursor-pointer hover:scale-110 transition-transform'
              )}
              onClick={() => handleStarClick(starIndex)}
              onMouseEnter={() => handleStarHover(starIndex)}
            />
          );
        })}
      </div>
      
      {showValue && (
        <span className={cn('text-muted-foreground ml-1', textSizeClasses[size])}>
          ({rating.toFixed(1)})
        </span>
      )}
    </div>
  );
}

export default RatingStars;

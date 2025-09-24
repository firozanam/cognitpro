import React from 'react';
import { Link } from '@inertiajs/react';
import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Star, ShoppingCart, Eye, Heart } from 'lucide-react';
import { formatPrice, formatRating } from '@/lib/utils';

interface Prompt {
  id: number;
  uuid: string;
  title: string;
  description: string;
  excerpt: string;
  price: number;
  price_type: 'fixed' | 'pay_what_you_want' | 'free';
  minimum_price?: number;
  featured: boolean;
  user: {
    id: number;
    name: string;
    email: string;
  };
  category: {
    id: number;
    name: string;
    slug: string;
    color: string;
  };
  tags: Array<{
    id: number;
    name: string;
    slug: string;
  }>;
  average_rating?: number;
  purchase_count?: number;
  is_purchased?: boolean;
}

interface PromptCardProps {
  prompt: Prompt;
  showActions?: boolean;
  className?: string;
}

export function PromptCard({ prompt, showActions = true, className = '' }: PromptCardProps) {

  const getUserInitials = (name: string) => {
    return name
      .split(' ')
      .map(word => word.charAt(0))
      .join('')
      .toUpperCase()
      .slice(0, 2);
  };

  return (
    <Card className={`group hover:shadow-lg transition-all duration-200 ${className}`}>
      <CardHeader className="pb-3">
        <div className="flex items-start justify-between">
          <div className="flex-1">
            <div className="flex items-center gap-2 mb-2">
              <Badge 
                variant="secondary" 
                className="text-xs"
                style={{ backgroundColor: `${prompt.category.color}20`, color: prompt.category.color }}
              >
                {prompt.category.name}
              </Badge>
              {prompt.featured && (
                <Badge variant="default" className="text-xs bg-yellow-500 hover:bg-yellow-600">
                  Featured
                </Badge>
              )}
            </div>
            <Link 
              href={`/prompts/${prompt.uuid}`}
              className="block group-hover:text-primary transition-colors"
            >
              <h3 className="font-semibold text-lg line-clamp-2 leading-tight">
                {prompt.title}
              </h3>
            </Link>
          </div>
          <div className="text-right">
            <div className="text-lg font-bold text-primary">
              {formatPrice(prompt.price, prompt.price_type, prompt.minimum_price)}
            </div>
          </div>
        </div>
      </CardHeader>

      <CardContent className="pb-3">
        <p className="text-sm text-muted-foreground line-clamp-3 mb-4">
          {prompt.excerpt || prompt.description}
        </p>

        {/* Tags */}
        {prompt.tags && prompt.tags.length > 0 && (
          <div className="flex flex-wrap gap-1 mb-4">
            {prompt.tags.slice(0, 3).map((tag) => (
              <Badge key={tag.id} variant="outline" className="text-xs">
                {tag.name}
              </Badge>
            ))}
            {prompt.tags.length > 3 && (
              <Badge variant="outline" className="text-xs">
                +{prompt.tags.length - 3} more
              </Badge>
            )}
          </div>
        )}

        {/* Stats */}
        <div className="flex items-center justify-between text-sm text-muted-foreground">
          <div className="flex items-center gap-4">
            {prompt.average_rating && (
              <div className="flex items-center gap-1">
                <Star className="h-4 w-4 fill-yellow-400 text-yellow-400" />
                <span>{formatRating(prompt.average_rating)}</span>
              </div>
            )}
            {prompt.purchase_count !== undefined && (
              <div className="flex items-center gap-1">
                <ShoppingCart className="h-4 w-4" />
                <span>{prompt.purchase_count}</span>
              </div>
            )}
          </div>
          
          {/* Author */}
          <div className="flex items-center gap-2">
            <Avatar className="h-6 w-6">
              <AvatarImage src={`https://api.dicebear.com/7.x/initials/svg?seed=${prompt.user.name}`} />
              <AvatarFallback className="text-xs">
                {getUserInitials(prompt.user.name)}
              </AvatarFallback>
            </Avatar>
            <span className="text-xs">{prompt.user.name}</span>
          </div>
        </div>
      </CardContent>

      {showActions && (
        <CardFooter className="pt-3">
          <div className="flex gap-2 w-full">
            <Button asChild className="flex-1">
              <Link href={`/prompts/${prompt.uuid}`}>
                <Eye className="h-4 w-4 mr-2" />
                View Details
              </Link>
            </Button>
            {!prompt.is_purchased && (
              <Button variant="outline" size="icon">
                <Heart className="h-4 w-4" />
              </Button>
            )}
          </div>
        </CardFooter>
      )}
    </Card>
  );
}

export default PromptCard;

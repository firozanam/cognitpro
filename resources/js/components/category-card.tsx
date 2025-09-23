import React from 'react';
import { Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ChevronRight } from 'lucide-react';

interface Category {
  id: number;
  uuid: string;
  name: string;
  slug: string;
  description?: string;
  icon?: string;
  color: string;
  is_active: boolean;
  sort_order: number;
  prompts_count?: number;
  children?: Category[];
}

interface CategoryCardProps {
  category: Category;
  showPromptCount?: boolean;
  showChildren?: boolean;
  className?: string;
}

export function CategoryCard({ 
  category, 
  showPromptCount = true, 
  showChildren = false,
  className = '' 
}: CategoryCardProps) {
  return (
    <Card className={`group hover:shadow-lg transition-all duration-200 cursor-pointer ${className}`}>
      <Link href={`/categories/${category.slug}`} className="block">
        <CardHeader className="pb-3">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-3">
              {category.icon && (
                <div 
                  className="w-10 h-10 rounded-lg flex items-center justify-center text-white text-lg font-semibold"
                  style={{ backgroundColor: category.color }}
                >
                  {category.icon}
                </div>
              )}
              <div>
                <h3 className="font-semibold text-lg group-hover:text-primary transition-colors">
                  {category.name}
                </h3>
                {showPromptCount && category.prompts_count !== undefined && (
                  <p className="text-sm text-muted-foreground">
                    {category.prompts_count} prompt{category.prompts_count !== 1 ? 's' : ''}
                  </p>
                )}
              </div>
            </div>
            <ChevronRight className="h-5 w-5 text-muted-foreground group-hover:text-primary transition-colors" />
          </div>
        </CardHeader>

        {(category.description || (showChildren && category.children)) && (
          <CardContent>
            {category.description && (
              <p className="text-sm text-muted-foreground mb-3 line-clamp-2">
                {category.description}
              </p>
            )}

            {showChildren && category.children && category.children.length > 0 && (
              <div className="flex flex-wrap gap-1">
                {category.children.slice(0, 4).map((child) => (
                  <Badge 
                    key={child.id} 
                    variant="outline" 
                    className="text-xs"
                    style={{ borderColor: child.color, color: child.color }}
                  >
                    {child.name}
                  </Badge>
                ))}
                {category.children.length > 4 && (
                  <Badge variant="outline" className="text-xs">
                    +{category.children.length - 4} more
                  </Badge>
                )}
              </div>
            )}
          </CardContent>
        )}
      </Link>
    </Card>
  );
}

export default CategoryCard;

import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import PromptCard from '@/components/prompt-card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { 
  ArrowLeft, 
  Grid, 
  List, 
  SortAsc, 
  SortDesc,
  Layers,
  ChevronRight
} from 'lucide-react';

interface Category {
  id: number;
  uuid: string;
  name: string;
  slug: string;
  description?: string;
  icon?: string;
  color: string;
  prompts_count: number;
}

interface Subcategory {
  id: number;
  name: string;
  slug: string;
  prompts_count: number;
}

interface Prompt {
  id: number;
  uuid: string;
  title: string;
  description: string;
  excerpt: string;
  price: number;
  price_type: string;
  minimum_price?: number;
  featured: boolean;
  published_at: string;
  average_rating: number;
  purchase_count: number;
  user: {
    id: number;
    name: string;
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
}

interface CategoryShowProps {
  category: Category;
  subcategories: Subcategory[];
  prompts: {
    data: Prompt[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  filters: {
    sort_by: string;
    sort_order: string;
  };
}

export default function CategoryShow({ category, subcategories, prompts, filters }: CategoryShowProps) {
  const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');

  const handleSortChange = (sortBy: string) => {
    const newSortOrder = filters.sort_by === sortBy && filters.sort_order === 'desc' ? 'asc' : 'desc';
    
    router.get(`/categories/${category.slug}`, {
      sort_by: sortBy,
      sort_order: newSortOrder,
    }, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const handlePageChange = (page: number) => {
    router.get(`/categories/${category.slug}`, {
      ...filters,
      page,
    }, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  return (
    <AppLayout>
      <Head title={`${category.name} - Categories - CognitPro`} />

      <div className="container mx-auto px-4 py-8">
        {/* Breadcrumb */}
        <nav className="flex items-center gap-2 text-sm text-muted-foreground mb-6">
          <Link href="/categories" className="hover:text-primary transition-colors">
            Categories
          </Link>
          <ChevronRight className="h-4 w-4" />
          <span className="text-foreground">{category.name}</span>
        </nav>

        {/* Category Header */}
        <div className="mb-8">
          <div className="flex items-start gap-4 mb-4">
            {category.icon && (
              <div 
                className="w-16 h-16 rounded-xl flex items-center justify-center text-white text-2xl font-bold flex-shrink-0"
                style={{ backgroundColor: category.color }}
              >
                {category.icon}
              </div>
            )}
            <div className="flex-1">
              <h1 className="text-3xl font-bold mb-2">{category.name}</h1>
              {category.description && (
                <p className="text-lg text-muted-foreground mb-4">{category.description}</p>
              )}
              <div className="flex items-center gap-4 text-sm text-muted-foreground">
                <span>{category.prompts_count} prompts</span>
                {subcategories.length > 0 && (
                  <span>{subcategories.length} subcategories</span>
                )}
              </div>
            </div>
          </div>

          <Button asChild variant="outline" size="sm">
            <Link href="/categories">
              <ArrowLeft className="h-4 w-4 mr-2" />
              Back to Categories
            </Link>
          </Button>
        </div>

        {/* Subcategories */}
        {subcategories.length > 0 && (
          <Card className="mb-8">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Layers className="h-5 w-5" />
                Subcategories
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                {subcategories.map((subcategory) => (
                  <Link
                    key={subcategory.id}
                    href={`/categories/${subcategory.slug}`}
                    className="flex items-center justify-between p-3 border rounded-lg hover:bg-muted/50 transition-colors group"
                  >
                    <span className="font-medium group-hover:text-primary transition-colors">
                      {subcategory.name}
                    </span>
                    <Badge variant="secondary">
                      {subcategory.prompts_count}
                    </Badge>
                  </Link>
                ))}
              </div>
            </CardContent>
          </Card>
        )}

        {/* Controls */}
        <div className="flex items-center justify-between mb-6">
          <div className="flex items-center gap-4">
            <span className="text-sm text-muted-foreground">
              {prompts.total.toLocaleString()} prompts
            </span>
          </div>

          <div className="flex items-center gap-4">
            {/* Sort Controls */}
            <div className="flex items-center gap-2">
              <span className="text-sm text-muted-foreground">Sort by:</span>
              <Select value={filters.sort_by} onValueChange={handleSortChange}>
                <SelectTrigger className="w-40">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="created_at">Newest</SelectItem>
                  <SelectItem value="popularity">Most Popular</SelectItem>
                  <SelectItem value="rating">Highest Rated</SelectItem>
                  <SelectItem value="price">Price</SelectItem>
                </SelectContent>
              </Select>
              <Button
                variant="ghost"
                size="sm"
                onClick={() => handleSortChange(filters.sort_by)}
              >
                {filters.sort_order === 'desc' ? (
                  <SortDesc className="h-4 w-4" />
                ) : (
                  <SortAsc className="h-4 w-4" />
                )}
              </Button>
            </div>

            {/* View Mode Toggle */}
            <div className="flex items-center border rounded-lg">
              <Button
                variant={viewMode === 'grid' ? 'default' : 'ghost'}
                size="sm"
                onClick={() => setViewMode('grid')}
                className="rounded-r-none"
              >
                <Grid className="h-4 w-4" />
              </Button>
              <Button
                variant={viewMode === 'list' ? 'default' : 'ghost'}
                size="sm"
                onClick={() => setViewMode('list')}
                className="rounded-l-none"
              >
                <List className="h-4 w-4" />
              </Button>
            </div>
          </div>
        </div>

        {/* Prompts */}
        {prompts.data.length > 0 ? (
          <>
            <div className={viewMode === 'grid' 
              ? "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" 
              : "space-y-4"
            }>
              {prompts.data.map((prompt) => (
                <PromptCard 
                  key={prompt.id} 
                  prompt={prompt} 
                  className={viewMode === 'list' ? 'flex-row' : ''}
                />
              ))}
            </div>

            {/* Pagination */}
            {prompts.last_page > 1 && (
              <div className="flex items-center justify-center gap-2 mt-8">
                <Button
                  variant="outline"
                  disabled={prompts.current_page === 1}
                  onClick={() => handlePageChange(prompts.current_page - 1)}
                >
                  Previous
                </Button>
                
                <span className="text-sm text-muted-foreground px-4">
                  Page {prompts.current_page} of {prompts.last_page}
                </span>
                
                <Button
                  variant="outline"
                  disabled={prompts.current_page === prompts.last_page}
                  onClick={() => handlePageChange(prompts.current_page + 1)}
                >
                  Next
                </Button>
              </div>
            )}
          </>
        ) : (
          <div className="text-center py-16">
            <Layers className="h-16 w-16 mx-auto text-muted-foreground mb-4" />
            <h3 className="text-xl font-semibold mb-2">No Prompts Found</h3>
            <p className="text-muted-foreground mb-6">
              This category doesn't have any prompts yet. Check back soon!
            </p>
            <Button asChild>
              <Link href="/prompts">Browse All Prompts</Link>
            </Button>
          </div>
        )}
      </div>
    </AppLayout>
  );
}

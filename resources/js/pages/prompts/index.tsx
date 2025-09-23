import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import PromptCard from '@/components/prompt-card';
import SearchFilters from '@/components/search-filters';
import { Search, Grid, List, Filter } from 'lucide-react';

interface PromptsIndexProps {
  prompts: {
    data: any[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  categories: any[];
  tags: any[];
  filters: {
    search?: string;
    category_id?: number;
    tags?: string[];
    price_type?: string;
    min_price?: number;
    max_price?: number;
    sort_by?: string;
    sort_order?: string;
    featured?: boolean;
  };
}

export default function PromptsIndex({ prompts, categories, tags, filters }: PromptsIndexProps) {
  const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');
  const [showFilters, setShowFilters] = useState(false);

  const handleFiltersChange = (newFilters: any) => {
    router.get('/prompts', newFilters, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const handleSearch = (searchTerm: string) => {
    handleFiltersChange({
      ...filters,
      search: searchTerm,
    });
  };

  const handlePageChange = (page: number) => {
    router.get('/prompts', {
      ...filters,
      page,
    }, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  return (
    <AppLayout>
      <Head title="Browse Prompts - CognitPro" />

      <div className="container mx-auto px-4 py-8">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold mb-2">Browse Prompts</h1>
          <p className="text-muted-foreground">
            Discover {prompts.total.toLocaleString()} high-quality AI prompts from our community
          </p>
        </div>

        {/* Search and Controls */}
        <div className="mb-6 space-y-4">
          {/* Search Bar */}
          <div className="relative max-w-2xl">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-5 w-5" />
            <Input 
              placeholder="Search prompts..."
              value={filters.search || ''}
              onChange={(e) => handleSearch(e.target.value)}
              className="pl-10"
            />
          </div>

          {/* Controls */}
          <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div className="flex items-center gap-4">
              <Button
                variant="outline"
                size="sm"
                onClick={() => setShowFilters(!showFilters)}
              >
                <Filter className="h-4 w-4 mr-2" />
                Filters
              </Button>
              
              <div className="flex items-center gap-2">
                <span className="text-sm text-muted-foreground">View:</span>
                <div className="flex border rounded-md">
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

            <div className="flex items-center gap-4">
              <div className="flex items-center gap-2">
                <span className="text-sm text-muted-foreground">Sort by:</span>
                <Select 
                  value={`${filters.sort_by || 'created_at'}_${filters.sort_order || 'desc'}`}
                  onValueChange={(value) => {
                    const [sort_by, sort_order] = value.split('_');
                    handleFiltersChange({
                      ...filters,
                      sort_by,
                      sort_order,
                    });
                  }}
                >
                  <SelectTrigger className="w-48">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="created_at_desc">Newest First</SelectItem>
                    <SelectItem value="created_at_asc">Oldest First</SelectItem>
                    <SelectItem value="popularity_desc">Most Popular</SelectItem>
                    <SelectItem value="rating_desc">Highest Rated</SelectItem>
                    <SelectItem value="price_asc">Price: Low to High</SelectItem>
                    <SelectItem value="price_desc">Price: High to Low</SelectItem>
                    <SelectItem value="title_asc">Title: A to Z</SelectItem>
                    <SelectItem value="title_desc">Title: Z to A</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
          </div>
        </div>

        <div className="flex gap-8">
          {/* Filters Sidebar */}
          {showFilters && (
            <div className="w-80 flex-shrink-0">
              <SearchFilters
                categories={categories}
                tags={tags}
                filters={filters}
                onFiltersChange={handleFiltersChange}
              />
            </div>
          )}

          {/* Main Content */}
          <div className="flex-1">
            {/* Results Info */}
            <div className="mb-6 flex items-center justify-between">
              <p className="text-sm text-muted-foreground">
                Showing {((prompts.current_page - 1) * prompts.per_page) + 1} to{' '}
                {Math.min(prompts.current_page * prompts.per_page, prompts.total)} of{' '}
                {prompts.total.toLocaleString()} results
              </p>
            </div>

            {/* Prompts Grid/List */}
            {prompts.data.length > 0 ? (
              <div className={
                viewMode === 'grid' 
                  ? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6'
                  : 'space-y-4'
              }>
                {prompts.data.map((prompt) => (
                  <PromptCard 
                    key={prompt.id} 
                    prompt={prompt}
                    className={viewMode === 'list' ? 'flex-row' : ''}
                  />
                ))}
              </div>
            ) : (
              <div className="text-center py-12">
                <p className="text-lg text-muted-foreground mb-4">
                  No prompts found matching your criteria
                </p>
                <Button onClick={() => handleFiltersChange({})}>
                  Clear Filters
                </Button>
              </div>
            )}

            {/* Pagination */}
            {prompts.last_page > 1 && (
              <div className="mt-8 flex justify-center">
                <div className="flex items-center gap-2">
                  <Button
                    variant="outline"
                    disabled={prompts.current_page === 1}
                    onClick={() => handlePageChange(prompts.current_page - 1)}
                  >
                    Previous
                  </Button>
                  
                  {Array.from({ length: Math.min(5, prompts.last_page) }, (_, i) => {
                    const page = i + 1;
                    return (
                      <Button
                        key={page}
                        variant={prompts.current_page === page ? 'default' : 'outline'}
                        onClick={() => handlePageChange(page)}
                      >
                        {page}
                      </Button>
                    );
                  })}
                  
                  <Button
                    variant="outline"
                    disabled={prompts.current_page === prompts.last_page}
                    onClick={() => handlePageChange(prompts.current_page + 1)}
                  >
                    Next
                  </Button>
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    </AppLayout>
  );
}

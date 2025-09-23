import React, { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { Badge } from '@/components/ui/badge';
import { ChevronDown, Filter, X } from 'lucide-react';
import { TagBadge } from './tag-badge';

interface Category {
  id: number;
  name: string;
  slug: string;
}

interface Tag {
  id: number;
  name: string;
  slug: string;
}

interface SearchFiltersProps {
  categories: Category[];
  tags: Tag[];
  filters: {
    search?: string;
    category_id?: number;
    tags?: string[];
    price_type?: string;
    min_price?: number;
    max_price?: number;
    sort_by?: string;
    sort_order?: string;
  };
  onFiltersChange: (filters: any) => void;
  className?: string;
}

export function SearchFilters({
  categories,
  tags,
  filters,
  onFiltersChange,
  className = ''
}: SearchFiltersProps) {
  const [isOpen, setIsOpen] = useState(false);

  const handleFilterChange = (key: string, value: any) => {
    onFiltersChange({
      ...filters,
      [key]: value
    });
  };

  const handleTagToggle = (tag: Tag) => {
    const currentTags = filters.tags || [];
    const tagSlug = tag.slug;
    
    if (currentTags.includes(tagSlug)) {
      handleFilterChange('tags', currentTags.filter(t => t !== tagSlug));
    } else {
      handleFilterChange('tags', [...currentTags, tagSlug]);
    }
  };

  const handleTagRemove = (tag: Tag) => {
    const currentTags = filters.tags || [];
    handleFilterChange('tags', currentTags.filter(t => t !== tag.slug));
  };

  const clearFilters = () => {
    onFiltersChange({
      search: '',
      category_id: undefined,
      tags: [],
      price_type: undefined,
      min_price: undefined,
      max_price: undefined,
      sort_by: 'created_at',
      sort_order: 'desc'
    });
  };

  const hasActiveFilters = Object.values(filters).some(value => 
    value !== undefined && value !== '' && (Array.isArray(value) ? value.length > 0 : true)
  );

  return (
    <Card className={className}>
      <CardHeader className="pb-3">
        <div className="flex items-center justify-between">
          <CardTitle className="text-lg flex items-center gap-2">
            <Filter className="h-5 w-5" />
            Filters
          </CardTitle>
          <div className="flex items-center gap-2">
            {hasActiveFilters && (
              <Button variant="ghost" size="sm" onClick={clearFilters}>
                <X className="h-4 w-4 mr-1" />
                Clear
              </Button>
            )}
            <CollapsibleTrigger asChild>
              <Button 
                variant="ghost" 
                size="sm"
                onClick={() => setIsOpen(!isOpen)}
              >
                <ChevronDown className={`h-4 w-4 transition-transform ${isOpen ? 'rotate-180' : ''}`} />
              </Button>
            </CollapsibleTrigger>
          </div>
        </div>
      </CardHeader>

      <Collapsible open={isOpen} onOpenChange={setIsOpen}>
        <CollapsibleContent>
          <CardContent className="space-y-4">
            {/* Search */}
            <div>
              <Label htmlFor="search">Search</Label>
              <Input
                id="search"
                placeholder="Search prompts..."
                value={filters.search || ''}
                onChange={(e) => handleFilterChange('search', e.target.value)}
              />
            </div>

            {/* Category */}
            <div>
              <Label>Category</Label>
              <Select 
                value={filters.category_id?.toString() || ''} 
                onValueChange={(value) => handleFilterChange('category_id', value ? parseInt(value) : undefined)}
              >
                <SelectTrigger>
                  <SelectValue placeholder="All categories" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">All categories</SelectItem>
                  {categories.map((category) => (
                    <SelectItem key={category.id} value={category.id.toString()}>
                      {category.name}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            {/* Price Type */}
            <div>
              <Label>Price Type</Label>
              <Select 
                value={filters.price_type || ''} 
                onValueChange={(value) => handleFilterChange('price_type', value || undefined)}
              >
                <SelectTrigger>
                  <SelectValue placeholder="All types" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">All types</SelectItem>
                  <SelectItem value="free">Free</SelectItem>
                  <SelectItem value="fixed">Fixed Price</SelectItem>
                  <SelectItem value="pay_what_you_want">Pay What You Want</SelectItem>
                </SelectContent>
              </Select>
            </div>

            {/* Price Range */}
            <div className="grid grid-cols-2 gap-2">
              <div>
                <Label htmlFor="min_price">Min Price</Label>
                <Input
                  id="min_price"
                  type="number"
                  placeholder="0"
                  value={filters.min_price || ''}
                  onChange={(e) => handleFilterChange('min_price', e.target.value ? parseFloat(e.target.value) : undefined)}
                />
              </div>
              <div>
                <Label htmlFor="max_price">Max Price</Label>
                <Input
                  id="max_price"
                  type="number"
                  placeholder="100"
                  value={filters.max_price || ''}
                  onChange={(e) => handleFilterChange('max_price', e.target.value ? parseFloat(e.target.value) : undefined)}
                />
              </div>
            </div>

            {/* Sort */}
            <div className="grid grid-cols-2 gap-2">
              <div>
                <Label>Sort By</Label>
                <Select 
                  value={filters.sort_by || 'created_at'} 
                  onValueChange={(value) => handleFilterChange('sort_by', value)}
                >
                  <SelectTrigger>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="created_at">Date Created</SelectItem>
                    <SelectItem value="popularity">Popularity</SelectItem>
                    <SelectItem value="rating">Rating</SelectItem>
                    <SelectItem value="price">Price</SelectItem>
                    <SelectItem value="title">Title</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div>
                <Label>Order</Label>
                <Select 
                  value={filters.sort_order || 'desc'} 
                  onValueChange={(value) => handleFilterChange('sort_order', value)}
                >
                  <SelectTrigger>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="desc">Descending</SelectItem>
                    <SelectItem value="asc">Ascending</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>

            {/* Tags */}
            <div>
              <Label>Tags</Label>
              <div className="flex flex-wrap gap-1 mt-2 mb-2">
                {(filters.tags || []).map((tagSlug) => {
                  const tag = tags.find(t => t.slug === tagSlug);
                  return tag ? (
                    <TagBadge
                      key={tag.id}
                      tag={tag}
                      removable
                      onRemove={handleTagRemove}
                    />
                  ) : null;
                })}
              </div>
              <div className="flex flex-wrap gap-1 max-h-32 overflow-y-auto">
                {tags.map((tag) => (
                  <TagBadge
                    key={tag.id}
                    tag={tag}
                    variant={(filters.tags || []).includes(tag.slug) ? 'default' : 'outline'}
                    interactive
                    onClick={() => handleTagToggle(tag)}
                  />
                ))}
              </div>
            </div>
          </CardContent>
        </CollapsibleContent>
      </Collapsible>
    </Card>
  );
}

export default SearchFilters;

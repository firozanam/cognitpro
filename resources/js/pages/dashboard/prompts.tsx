import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
  Plus, 
  Search, 
  Filter, 
  Edit, 
  Eye, 
  Archive, 
  Star,
  DollarSign,
  Calendar,
  MoreHorizontal,
  SortAsc,
  SortDesc
} from 'lucide-react';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface Prompt {
  id: number;
  uuid: string;
  title: string;
  description: string;
  excerpt: string;
  price: number;
  price_type: 'fixed' | 'pay_what_you_want' | 'free';
  minimum_price?: number;
  status: 'draft' | 'published' | 'archived';
  featured: boolean;
  created_at: string;
  updated_at: string;
  published_at?: string;
  average_rating: number;
  purchase_count: number;
  category?: {
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

interface PromptsManageProps {
  prompts: {
    data: Prompt[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  filters: {
    status?: string;
    search?: string;
    sort_by: string;
    sort_order: string;
  };
}

export default function PromptsManage({ prompts, filters }: PromptsManageProps) {
  const [searchQuery, setSearchQuery] = useState(filters.search || '');

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    router.get('/dashboard/prompts', {
      ...filters,
      search: searchQuery,
    }, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const handleFilterChange = (key: string, value: string) => {
    router.get('/dashboard/prompts', {
      ...filters,
      [key]: value,
    }, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const handleSortChange = (sortBy: string) => {
    const newSortOrder = filters.sort_by === sortBy && filters.sort_order === 'desc' ? 'asc' : 'desc';
    
    router.get('/dashboard/prompts', {
      ...filters,
      sort_by: sortBy,
      sort_order: newSortOrder,
    }, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const handlePageChange = (page: number) => {
    router.get('/dashboard/prompts', {
      ...filters,
      page,
    }, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'published':
        return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
      case 'draft':
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
      case 'archived':
        return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
      default:
        return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
  };

  const formatPrice = (prompt: Prompt) => {
    if (prompt.price_type === 'free') return 'Free';
    if (prompt.price_type === 'pay_what_you_want') {
      return `$${prompt.minimum_price?.toFixed(2) || '0.00'}+`;
    }
    return `$${prompt.price.toFixed(2)}`;
  };

  return (
    <AppLayout>
      <Head title="My Prompts - CognitPro" />

      <div className="container mx-auto px-4 py-8">
        {/* Header */}
        <div className="flex items-center justify-between mb-8">
          <div>
            <h1 className="text-3xl font-bold">My Prompts</h1>
            <p className="text-muted-foreground">
              Manage your AI prompts and track their performance
            </p>
          </div>
          
          <Button asChild>
            <Link href="/prompts/create">
              <Plus className="h-4 w-4 mr-2" />
              Create New Prompt
            </Link>
          </Button>
        </div>

        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-muted-foreground">Total Prompts</p>
                  <p className="text-2xl font-bold">{prompts.total}</p>
                </div>
                <div className="h-8 w-8 bg-primary/10 rounded-lg flex items-center justify-center">
                  <Eye className="h-4 w-4 text-primary" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-muted-foreground">Published</p>
                  <p className="text-2xl font-bold">
                    {prompts.data.filter(p => p.status === 'published').length}
                  </p>
                </div>
                <div className="h-8 w-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                  <Star className="h-4 w-4 text-green-600 dark:text-green-400" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-muted-foreground">Total Sales</p>
                  <p className="text-2xl font-bold">
                    {prompts.data.reduce((sum, p) => sum + p.purchase_count, 0)}
                  </p>
                </div>
                <div className="h-8 w-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                  <DollarSign className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-muted-foreground">Avg. Rating</p>
                  <p className="text-2xl font-bold">
                    {prompts.data.length > 0 
                      ? (prompts.data.reduce((sum, p) => sum + p.average_rating, 0) / prompts.data.length).toFixed(1)
                      : '0.0'
                    }
                  </p>
                </div>
                <div className="h-8 w-8 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                  <Star className="h-4 w-4 text-yellow-600 dark:text-yellow-400" />
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Filters and Search */}
        <Card className="mb-6">
          <CardContent className="p-6">
            <div className="flex flex-col sm:flex-row gap-4">
              <form onSubmit={handleSearch} className="flex-1">
                <div className="relative">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                  <Input
                    placeholder="Search prompts..."
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    className="pl-10"
                  />
                </div>
              </form>

              <div className="flex items-center gap-2">
                <Select value={filters.status || 'all'} onValueChange={(value) => handleFilterChange('status', value === 'all' ? '' : value)}>
                  <SelectTrigger className="w-40">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Status</SelectItem>
                    <SelectItem value="published">Published</SelectItem>
                    <SelectItem value="draft">Draft</SelectItem>
                    <SelectItem value="archived">Archived</SelectItem>
                  </SelectContent>
                </Select>

                <Select value={filters.sort_by} onValueChange={(value) => handleSortChange(value)}>
                  <SelectTrigger className="w-40">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="created_at">Newest</SelectItem>
                    <SelectItem value="updated_at">Recently Updated</SelectItem>
                    <SelectItem value="title">Title</SelectItem>
                    <SelectItem value="popularity">Most Popular</SelectItem>
                    <SelectItem value="rating">Highest Rated</SelectItem>
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
            </div>
          </CardContent>
        </Card>

        {/* Prompts List */}
        {prompts.data.length > 0 ? (
          <>
            <div className="space-y-4">
              {prompts.data.map((prompt) => (
                <Card key={prompt.id}>
                  <CardContent className="p-6">
                    <div className="flex items-start justify-between">
                      <div className="flex-1">
                        <div className="flex items-center gap-3 mb-2">
                          <h3 className="text-lg font-semibold">{prompt.title}</h3>
                          <Badge className={getStatusColor(prompt.status)}>
                            {prompt.status.charAt(0).toUpperCase() + prompt.status.slice(1)}
                          </Badge>
                          {prompt.featured && (
                            <Badge variant="outline">
                              <Star className="h-3 w-3 mr-1" />
                              Featured
                            </Badge>
                          )}
                        </div>

                        <p className="text-muted-foreground mb-3 line-clamp-2">
                          {prompt.description}
                        </p>

                        <div className="flex items-center gap-6 text-sm text-muted-foreground">
                          <div className="flex items-center gap-1">
                            <DollarSign className="h-4 w-4" />
                            <span>{formatPrice(prompt)}</span>
                          </div>
                          <div className="flex items-center gap-1">
                            <Eye className="h-4 w-4" />
                            <span>{prompt.purchase_count} sales</span>
                          </div>
                          <div className="flex items-center gap-1">
                            <Star className="h-4 w-4" />
                            <span>{prompt.average_rating.toFixed(1)} rating</span>
                          </div>
                          <div className="flex items-center gap-1">
                            <Calendar className="h-4 w-4" />
                            <span>Updated {new Date(prompt.updated_at).toLocaleDateString()}</span>
                          </div>
                        </div>

                        {prompt.category && (
                          <div className="mt-3">
                            <Badge 
                              variant="outline"
                              style={{ 
                                backgroundColor: `${prompt.category.color}20`, 
                                color: prompt.category.color,
                                borderColor: `${prompt.category.color}40`
                              }}
                            >
                              {prompt.category.name}
                            </Badge>
                          </div>
                        )}
                      </div>

                      <div className="flex items-center gap-2 ml-4">
                        <Button asChild variant="outline" size="sm">
                          <Link href={`/prompts/${prompt.uuid}`}>
                            <Eye className="h-4 w-4 mr-2" />
                            View
                          </Link>
                        </Button>
                        
                        <Button asChild variant="outline" size="sm">
                          <Link href={`/prompts/${prompt.uuid}/edit`}>
                            <Edit className="h-4 w-4 mr-2" />
                            Edit
                          </Link>
                        </Button>

                        <DropdownMenu>
                          <DropdownMenuTrigger asChild>
                            <Button variant="ghost" size="sm">
                              <MoreHorizontal className="h-4 w-4" />
                            </Button>
                          </DropdownMenuTrigger>
                          <DropdownMenuContent align="end">
                            <DropdownMenuItem>
                              <Archive className="h-4 w-4 mr-2" />
                              Archive
                            </DropdownMenuItem>
                          </DropdownMenuContent>
                        </DropdownMenu>
                      </div>
                    </div>
                  </CardContent>
                </Card>
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
          <Card>
            <CardContent className="p-12 text-center">
              <div className="max-w-md mx-auto">
                <div className="h-16 w-16 bg-muted rounded-full flex items-center justify-center mx-auto mb-4">
                  <Plus className="h-8 w-8 text-muted-foreground" />
                </div>
                <h3 className="text-xl font-semibold mb-2">No prompts yet</h3>
                <p className="text-muted-foreground mb-6">
                  Create your first AI prompt to start selling to the community.
                </p>
                <Button asChild>
                  <Link href="/prompts/create">
                    <Plus className="h-4 w-4 mr-2" />
                    Create Your First Prompt
                  </Link>
                </Button>
              </div>
            </CardContent>
          </Card>
        )}
      </div>
    </AppLayout>
  );
}

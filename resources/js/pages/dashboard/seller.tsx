import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import RatingStars from '@/components/rating-stars';
import { 
  Plus, 
  TrendingUp, 
  DollarSign, 
  Star, 
  FileText,
  Eye,
  ShoppingCart,
  MessageSquare,
  Calendar
} from 'lucide-react';
import { safeToFixed, formatRating } from '@/lib/utils';

interface SellerDashboardProps {
  prompts: any[];
  recentSales: any[];
  recentReviews: any[];
  stats: {
    total_prompts: number;
    published_prompts: number;
    draft_prompts: number;
    total_sales: number;
    total_revenue: number;
    average_rating: number;
    total_reviews: number;
  };
}

export default function SellerDashboard({ 
  prompts, 
  recentSales, 
  recentReviews, 
  stats 
}: SellerDashboardProps) {
  const formatPrice = (price: any) => {
    return `$${safeToFixed(price)}`;
  };

  const getUserInitials = (name: string) => {
    return name
      .split(' ')
      .map(word => word.charAt(0))
      .join('')
      .toUpperCase()
      .slice(0, 2);
  };

  return (
    <AppLayout>
      <Head title="Seller Dashboard - CognitPro" />

      <div className="container mx-auto px-4 py-8">
        {/* Header */}
        <div className="flex items-center justify-between mb-8">
          <div>
            <h1 className="text-3xl font-bold mb-2">Seller Dashboard</h1>
            <p className="text-muted-foreground">
              Manage your prompts and track your performance
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
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <Card>
            <CardContent className="pt-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-muted-foreground">Total Revenue</p>
                  <p className="text-2xl font-bold">{formatPrice(stats.total_revenue)}</p>
                </div>
                <DollarSign className="h-8 w-8 text-green-600" />
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="pt-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-muted-foreground">Total Sales</p>
                  <p className="text-2xl font-bold">{stats.total_sales}</p>
                </div>
                <ShoppingCart className="h-8 w-8 text-blue-600" />
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="pt-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-muted-foreground">Published Prompts</p>
                  <p className="text-2xl font-bold">{stats.published_prompts}</p>
                </div>
                <FileText className="h-8 w-8 text-primary" />
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="pt-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-muted-foreground">Average Rating</p>
                  <div className="flex items-center gap-2">
                    <p className="text-2xl font-bold">
                      {formatRating(stats.average_rating)}
                    </p>
                    <RatingStars rating={stats.average_rating || 0} size="sm" />
                  </div>
                </div>
                <Star className="h-8 w-8 text-yellow-600" />
              </div>
            </CardContent>
          </Card>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          {/* Recent Prompts */}
          <Card>
            <CardHeader>
              <div className="flex items-center justify-between">
                <CardTitle>Your Prompts</CardTitle>
                <Button asChild variant="outline" size="sm">
                  <Link href="/dashboard/prompts">View All</Link>
                </Button>
              </div>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {prompts.length > 0 ? (
                  prompts.map((prompt) => (
                    <div key={prompt.id} className="flex items-center justify-between p-3 border rounded-lg">
                      <div className="flex-1">
                        <div className="flex items-center gap-2 mb-1">
                          <Link 
                            href={`/prompts/${prompt.uuid}`}
                            className="font-medium hover:text-primary transition-colors"
                          >
                            {prompt.title}
                          </Link>
                          <Badge 
                            variant={prompt.status === 'published' ? 'default' : 'secondary'}
                          >
                            {prompt.status}
                          </Badge>
                          {prompt.featured && (
                            <Badge variant="outline" className="text-yellow-600 border-yellow-600">
                              Featured
                            </Badge>
                          )}
                        </div>
                        <div className="flex items-center gap-4 text-sm text-muted-foreground">
                          <span>{formatPrice(prompt.price)}</span>
                          <span>{prompt.purchase_count} sales</span>
                          {prompt.average_rating > 0 && (
                            <div className="flex items-center gap-1">
                              <RatingStars rating={prompt.average_rating} size="xs" />
                              <span>{formatRating(prompt.average_rating)}</span>
                            </div>
                          )}
                        </div>
                      </div>
                      <div className="flex items-center gap-2">
                        <Button asChild variant="ghost" size="sm">
                          <Link href={`/prompts/${prompt.uuid}/edit`}>
                            Edit
                          </Link>
                        </Button>
                      </div>
                    </div>
                  ))
                ) : (
                  <div className="text-center py-8">
                    <FileText className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                    <p className="text-muted-foreground mb-4">No prompts yet</p>
                    <Button asChild>
                      <Link href="/prompts/create">Create Your First Prompt</Link>
                    </Button>
                  </div>
                )}
              </div>
            </CardContent>
          </Card>

          {/* Recent Sales */}
          <Card>
            <CardHeader>
              <CardTitle>Recent Sales</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {recentSales.length > 0 ? (
                  recentSales.map((sale) => (
                    <div key={sale.id} className="flex items-center justify-between p-3 border rounded-lg">
                      <div className="flex-1">
                        <p className="font-medium">{sale.prompt.title}</p>
                        <div className="flex items-center gap-4 text-sm text-muted-foreground">
                          <span>by {sale.buyer.name}</span>
                          <span>{new Date(sale.purchased_at).toLocaleDateString()}</span>
                        </div>
                      </div>
                      <div className="text-right">
                        <p className="font-semibold text-green-600">
                          {formatPrice(sale.price_paid)}
                        </p>
                      </div>
                    </div>
                  ))
                ) : (
                  <div className="text-center py-8">
                    <ShoppingCart className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                    <p className="text-muted-foreground">No sales yet</p>
                  </div>
                )}
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Recent Reviews */}
        {recentReviews.length > 0 && (
          <Card className="mt-8">
            <CardHeader>
              <CardTitle>Recent Reviews</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {recentReviews.map((review) => (
                  <div key={review.id} className="p-4 border rounded-lg">
                    <div className="flex items-start gap-4">
                      <Avatar>
                        <AvatarImage src={`https://api.dicebear.com/7.x/initials/svg?seed=${review.reviewer.name}`} />
                        <AvatarFallback>
                          {getUserInitials(review.reviewer.name)}
                        </AvatarFallback>
                      </Avatar>
                      <div className="flex-1">
                        <div className="flex items-center gap-2 mb-2">
                          <span className="font-medium">{review.reviewer.name}</span>
                          <RatingStars rating={review.rating} size="sm" />
                          <span className="text-sm text-muted-foreground">
                            on {review.prompt.title}
                          </span>
                        </div>
                        {review.title && (
                          <h4 className="font-medium mb-1">{review.title}</h4>
                        )}
                        <p className="text-muted-foreground">{review.review_text}</p>
                        <p className="text-xs text-muted-foreground mt-2">
                          {new Date(review.created_at).toLocaleDateString()}
                        </p>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        )}
      </div>
    </AppLayout>
  );
}

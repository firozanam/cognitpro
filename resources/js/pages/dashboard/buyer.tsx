import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import RatingStars from '@/components/rating-stars';
import { 
  ShoppingBag, 
  DollarSign, 
  Star, 
  Heart,
  Download,
  MessageSquare,
  TrendingUp,
  Calendar
} from 'lucide-react';
import { safeToFixed } from '@/lib/utils';

interface BuyerDashboardProps {
  purchases: any[];
  reviews: any[];
  stats: {
    total_purchases: number;
    total_spent: number;
    total_reviews: number;
    favorite_category: string;
  };
}

export default function BuyerDashboard({ 
  purchases, 
  reviews, 
  stats 
}: BuyerDashboardProps) {
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
      <Head title="Buyer Dashboard - CognitPro" />

      <div className="container mx-auto px-4 py-8">
        {/* Header */}
        <div className="flex items-center justify-between mb-8">
          <div>
            <h1 className="text-3xl font-bold mb-2">My Dashboard</h1>
            <p className="text-muted-foreground">
              Track your purchases and manage your AI prompt collection
            </p>
          </div>
          <Button asChild>
            <Link href="/prompts">
              <ShoppingBag className="h-4 w-4 mr-2" />
              Browse Prompts
            </Link>
          </Button>
        </div>

        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <Card>
            <CardContent className="pt-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-muted-foreground">Total Spent</p>
                  <p className="text-2xl font-bold">{formatPrice(stats.total_spent)}</p>
                </div>
                <DollarSign className="h-8 w-8 text-green-600" />
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="pt-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-muted-foreground">Prompts Owned</p>
                  <p className="text-2xl font-bold">{stats.total_purchases}</p>
                </div>
                <ShoppingBag className="h-8 w-8 text-blue-600" />
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="pt-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-muted-foreground">Reviews Written</p>
                  <p className="text-2xl font-bold">{stats.total_reviews}</p>
                </div>
                <MessageSquare className="h-8 w-8 text-primary" />
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="pt-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-muted-foreground">Favorite Category</p>
                  <p className="text-lg font-bold">{stats.favorite_category || 'None yet'}</p>
                </div>
                <Heart className="h-8 w-8 text-red-600" />
              </div>
            </CardContent>
          </Card>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          {/* Recent Purchases */}
          <Card>
            <CardHeader>
              <div className="flex items-center justify-between">
                <CardTitle>Your Prompts</CardTitle>
                <Button asChild variant="outline" size="sm">
                  <Link href="/dashboard/purchases">View All</Link>
                </Button>
              </div>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {purchases.length > 0 ? (
                  purchases.map((purchase) => (
                    <div key={purchase.id} className="flex items-center justify-between p-3 border rounded-lg">
                      <div className="flex-1">
                        <div className="flex items-center gap-2 mb-1">
                          <Link 
                            href={`/prompts/${purchase.prompt.uuid}`}
                            className="font-medium hover:text-primary transition-colors"
                          >
                            {purchase.prompt.title}
                          </Link>
                          {purchase.prompt.category && (
                            <Badge 
                              variant="secondary"
                              style={{ 
                                backgroundColor: `${purchase.prompt.category.color}20`, 
                                color: purchase.prompt.category.color 
                              }}
                            >
                              {purchase.prompt.category.name}
                            </Badge>
                          )}
                        </div>
                        <div className="flex items-center gap-4 text-sm text-muted-foreground">
                          <span>by {purchase.prompt.user.name}</span>
                          <span>{new Date(purchase.purchased_at).toLocaleDateString()}</span>
                        </div>
                      </div>
                      <div className="flex items-center gap-2">
                        <span className="font-semibold text-green-600">
                          {formatPrice(purchase.price_paid)}
                        </span>
                        <Button asChild variant="ghost" size="sm">
                          <Link href={`/prompts/${purchase.prompt.uuid}`}>
                            <Download className="h-4 w-4" />
                          </Link>
                        </Button>
                      </div>
                    </div>
                  ))
                ) : (
                  <div className="text-center py-8">
                    <ShoppingBag className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                    <p className="text-muted-foreground mb-4">No purchases yet</p>
                    <Button asChild>
                      <Link href="/prompts">Browse Prompts</Link>
                    </Button>
                  </div>
                )}
              </div>
            </CardContent>
          </Card>

          {/* Recent Reviews */}
          <Card>
            <CardHeader>
              <CardTitle>Your Reviews</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {reviews.length > 0 ? (
                  reviews.map((review) => (
                    <div key={review.id} className="p-3 border rounded-lg">
                      <div className="flex items-center justify-between mb-2">
                        <Link 
                          href={`/prompts/${review.prompt.id}`}
                          className="font-medium hover:text-primary transition-colors"
                        >
                          {review.prompt.title}
                        </Link>
                        <RatingStars rating={review.rating} size="sm" />
                      </div>
                      {review.title && (
                        <h4 className="font-medium mb-1">{review.title}</h4>
                      )}
                      <p className="text-sm text-muted-foreground mb-2">
                        {review.review_text}
                      </p>
                      <p className="text-xs text-muted-foreground">
                        {new Date(review.created_at).toLocaleDateString()}
                      </p>
                    </div>
                  ))
                ) : (
                  <div className="text-center py-8">
                    <MessageSquare className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                    <p className="text-muted-foreground">No reviews yet</p>
                    <p className="text-sm text-muted-foreground">
                      Purchase and review prompts to help the community
                    </p>
                  </div>
                )}
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Quick Actions */}
        <Card className="mt-8">
          <CardHeader>
            <CardTitle>Quick Actions</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <Button asChild variant="outline" className="h-20 flex-col">
                <Link href="/prompts">
                  <ShoppingBag className="h-6 w-6 mb-2" />
                  Browse New Prompts
                </Link>
              </Button>
              
              <Button asChild variant="outline" className="h-20 flex-col">
                <Link href="/categories">
                  <TrendingUp className="h-6 w-6 mb-2" />
                  Explore Categories
                </Link>
              </Button>
              
              <Button asChild variant="outline" className="h-20 flex-col">
                <Link href="/dashboard/purchases">
                  <Download className="h-6 w-6 mb-2" />
                  My Downloads
                </Link>
              </Button>
            </div>
          </CardContent>
        </Card>

        {/* Recommendations */}
        <Card className="mt-8">
          <CardHeader>
            <CardTitle>Recommended for You</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-center py-8">
              <Star className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
              <p className="text-muted-foreground mb-4">
                We'll show personalized recommendations based on your purchase history
              </p>
              <Button asChild>
                <Link href="/prompts">Discover Prompts</Link>
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}

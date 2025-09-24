import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import RatingStars from '@/components/rating-stars';
import { 
  MessageSquare, 
  Star, 
  TrendingUp,
  Calendar,
  User
} from 'lucide-react';
import { formatRating } from '@/lib/utils';

interface SellerReviewsProps {
  reviews: {
    data: Array<{
      id: number;
      rating: number;
      review_text: string;
      created_at: string;
      user: {
        id: number;
        name: string;
        email: string;
      };
      prompt: {
        id: number;
        title: string;
        uuid: string;
      };
    }>;
    links: any;
    meta: any;
  };
}

export default function SellerReviews({ reviews }: SellerReviewsProps) {
  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  };

  const getUserInitials = (name: string) => {
    return name
      .split(' ')
      .map(word => word.charAt(0))
      .join('')
      .toUpperCase()
      .slice(0, 2);
  };

  const averageRating = reviews.data.length > 0 
    ? reviews.data.reduce((sum, review) => sum + review.rating, 0) / reviews.data.length 
    : 0;

  const ratingDistribution = [5, 4, 3, 2, 1].map(rating => ({
    rating,
    count: reviews.data.filter(review => review.rating === rating).length,
    percentage: reviews.data.length > 0 
      ? (reviews.data.filter(review => review.rating === rating).length / reviews.data.length) * 100 
      : 0
  }));

  return (
    <AppLayout>
      <Head title="Reviews - CognitPro" />

      <div className="container mx-auto px-4 py-8">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold mb-2">Reviews</h1>
          <p className="text-muted-foreground">
            See what customers are saying about your prompts
          </p>
        </div>

        {/* Review Summary */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Average Rating</CardTitle>
              <Star className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="flex items-center gap-2">
                <div className="text-2xl font-bold">{formatRating(averageRating)}</div>
                <RatingStars rating={averageRating} size="sm" />
              </div>
              <p className="text-xs text-muted-foreground">
                From {reviews.data.length} reviews
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Reviews</CardTitle>
              <MessageSquare className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{reviews.data.length}</div>
              <p className="text-xs text-muted-foreground">
                Customer feedback
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">5-Star Reviews</CardTitle>
              <TrendingUp className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                {ratingDistribution.find(r => r.rating === 5)?.count || 0}
              </div>
              <p className="text-xs text-muted-foreground">
                {ratingDistribution.find(r => r.rating === 5)?.percentage.toFixed(1) || 0}% of total
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Rating Distribution */}
        <Card className="mb-8">
          <CardHeader>
            <CardTitle>Rating Distribution</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {ratingDistribution.map(({ rating, count, percentage }) => (
                <div key={rating} className="flex items-center gap-4">
                  <div className="flex items-center gap-1 w-16">
                    <span className="text-sm font-medium">{rating}</span>
                    <Star className="h-3 w-3 fill-current text-yellow-400" />
                  </div>
                  <div className="flex-1 bg-muted rounded-full h-2">
                    <div 
                      className="bg-primary h-2 rounded-full transition-all duration-300"
                      style={{ width: `${percentage}%` }}
                    />
                  </div>
                  <div className="text-sm text-muted-foreground w-12 text-right">
                    {count}
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        {/* Reviews List */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Calendar className="h-5 w-5" />
              Recent Reviews
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-6">
              {reviews.data.length > 0 ? (
                reviews.data.map((review) => (
                  <div key={review.id} className="border-b pb-6 last:border-b-0 last:pb-0">
                    <div className="flex items-start gap-4">
                      <Avatar>
                        <AvatarImage src={`https://api.dicebear.com/7.x/initials/svg?seed=${review.user.name}`} />
                        <AvatarFallback>
                          {getUserInitials(review.user.name)}
                        </AvatarFallback>
                      </Avatar>
                      
                      <div className="flex-1">
                        <div className="flex items-center justify-between mb-2">
                          <div>
                            <div className="font-medium">{review.user.name}</div>
                            <div className="text-sm text-muted-foreground">
                              {formatDate(review.created_at)}
                            </div>
                          </div>
                          <RatingStars rating={review.rating} size="sm" />
                        </div>
                        
                        <div className="mb-3">
                          <Link 
                            href={`/prompts/${review.prompt.uuid}`}
                            className="text-sm font-medium text-primary hover:underline"
                          >
                            {review.prompt.title}
                          </Link>
                        </div>
                        
                        {review.review_text && (
                          <div className="text-sm text-muted-foreground bg-muted/50 p-3 rounded-lg">
                            "{review.review_text}"
                          </div>
                        )}
                      </div>
                    </div>
                  </div>
                ))
              ) : (
                <div className="text-center py-12">
                  <MessageSquare className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                  <h3 className="text-lg font-medium mb-2">No reviews yet</h3>
                  <p className="text-muted-foreground mb-4">
                    Reviews from customers will appear here once they purchase and review your prompts.
                  </p>
                  <Link 
                    href="/prompts/create"
                    className="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-md hover:bg-primary/90 transition-colors"
                  >
                    Create Your First Prompt
                  </Link>
                </div>
              )}
            </div>

            {/* Pagination would go here if needed */}
            {reviews.links && (
              <div className="mt-6 flex justify-center">
                {/* Add pagination component here */}
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}

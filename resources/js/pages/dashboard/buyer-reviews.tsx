import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import RatingStars from '@/components/rating-stars';
import { 
  MessageSquare, 
  Star, 
  Calendar, 
  FileText,
  Edit,
  Trash2
} from 'lucide-react';
import { formatRating } from '@/lib/utils';

interface BuyerReviewsProps {
  reviews: {
    data: Array<{
      id: number;
      rating: number;
      review_text: string;
      created_at: string;
      updated_at: string;
      prompt: {
        id: number;
        title: string;
        uuid: string;
        user: {
          id: number;
          name: string;
        };
      };
    }>;
    links: any;
    meta: any;
  };
}

export default function BuyerReviews({ reviews }: BuyerReviewsProps) {
  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  };

  const averageRating = reviews.data.length > 0 
    ? reviews.data.reduce((sum, review) => sum + review.rating, 0) / reviews.data.length 
    : 0;

  const ratingDistribution = [5, 4, 3, 2, 1].map(rating => ({
    rating,
    count: reviews.data.filter(review => review.rating === rating).length
  }));

  return (
    <AppLayout>
      <Head title="My Reviews - CognitPro" />

      <div className="container mx-auto px-4 py-8">
        {/* Header */}
        <div className="flex items-center justify-between mb-8">
          <div>
            <h1 className="text-3xl font-bold mb-2">My Reviews</h1>
            <p className="text-muted-foreground">
              Manage the reviews you've written for purchased prompts
            </p>
          </div>
          <Button asChild>
            <Link href="/prompts">
              <MessageSquare className="h-4 w-4 mr-2" />
              Find More Prompts to Review
            </Link>
          </Button>
        </div>

        {/* Review Summary */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Reviews</CardTitle>
              <MessageSquare className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{reviews.data.length}</div>
              <p className="text-xs text-muted-foreground">
                Reviews written
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Average Rating Given</CardTitle>
              <Star className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="flex items-center gap-2">
                <div className="text-2xl font-bold">{formatRating(averageRating)}</div>
                <RatingStars rating={averageRating} size="sm" />
              </div>
              <p className="text-xs text-muted-foreground">
                Your average rating
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">5-Star Reviews</CardTitle>
              <Star className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                {ratingDistribution.find(r => r.rating === 5)?.count || 0}
              </div>
              <p className="text-xs text-muted-foreground">
                Excellent ratings given
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Reviews List */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Calendar className="h-5 w-5" />
              Your Reviews
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-6">
              {reviews.data.length > 0 ? (
                reviews.data.map((review) => (
                  <div key={review.id} className="border rounded-lg p-6">
                    <div className="flex items-start justify-between mb-4">
                      <div className="flex-1">
                        <div className="flex items-center gap-3 mb-2">
                          <Link 
                            href={`/prompts/${review.prompt.uuid}`}
                            className="text-lg font-semibold hover:underline"
                          >
                            {review.prompt.title}
                          </Link>
                          <RatingStars rating={review.rating} size="sm" />
                        </div>
                        
                        <div className="text-sm text-muted-foreground mb-3">
                          by {review.prompt.user.name} • Reviewed {formatDate(review.created_at)}
                          {review.updated_at !== review.created_at && (
                            <span> • Updated {formatDate(review.updated_at)}</span>
                          )}
                        </div>
                        
                        {review.review_text && (
                          <div className="bg-muted/50 p-4 rounded-lg mb-4">
                            <p className="text-sm">"{review.review_text}"</p>
                          </div>
                        )}
                      </div>
                      
                      <div className="flex gap-2 ml-6">
                        <Button size="sm" variant="outline">
                          <Edit className="h-4 w-4 mr-1" />
                          Edit
                        </Button>
                        <Button size="sm" variant="outline" className="text-destructive hover:text-destructive">
                          <Trash2 className="h-4 w-4 mr-1" />
                          Delete
                        </Button>
                      </div>
                    </div>
                  </div>
                ))
              ) : (
                <div className="text-center py-12">
                  <MessageSquare className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                  <h3 className="text-lg font-medium mb-2">No reviews yet</h3>
                  <p className="text-muted-foreground mb-6">
                    Start purchasing and reviewing prompts to help other users make informed decisions.
                  </p>
                  <div className="flex gap-4 justify-center">
                    <Button asChild>
                      <Link href="/prompts">
                        <FileText className="h-4 w-4 mr-2" />
                        Browse Prompts
                      </Link>
                    </Button>
                    <Button variant="outline" asChild>
                      <Link href="/dashboard/purchases">
                        <MessageSquare className="h-4 w-4 mr-2" />
                        Review Purchases
                      </Link>
                    </Button>
                  </div>
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

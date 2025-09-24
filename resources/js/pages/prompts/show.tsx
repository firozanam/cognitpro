import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Separator } from '@/components/ui/separator';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import RatingStars from '@/components/rating-stars';
import TagBadge from '@/components/tag-badge';
import { 
  ShoppingCart, 
  Heart, 
  Share2, 
  Star, 
  User, 
  Calendar, 
  Eye,
  Download,
  Shield,
  MessageCircle
} from 'lucide-react';
import { formatPrice, formatRating } from '@/lib/utils';

interface PromptShowProps {
  prompt: {
    id: number;
    uuid: string;
    title: string;
    description: string;
    content: string;
    excerpt: string;
    price: number;
    price_type: 'fixed' | 'pay_what_you_want' | 'free';
    minimum_price?: number;
    featured: boolean;
    version: number;
    published_at: string;
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
    reviews: any[];
    average_rating: number;
    purchase_count: number;
    is_purchased: boolean;
  };
  relatedPrompts: any[];
  auth?: {
    user?: any;
  };
}

export default function PromptShow({ prompt, relatedPrompts, auth }: PromptShowProps) {
  const [activeTab, setActiveTab] = useState('description');



  const getUserInitials = (name: string) => {
    return name
      .split(' ')
      .map(word => word.charAt(0))
      .join('')
      .toUpperCase()
      .slice(0, 2);
  };

  const handlePurchase = () => {
    if (!auth?.user) {
      router.visit('/login');
      return;
    }
    
    // Redirect to purchase flow
    router.visit(`/prompts/${prompt.uuid}/purchase`);
  };

  return (
    <AppLayout>
      <Head title={`${prompt.title} - CognitPro`} />

      <div className="container mx-auto px-4 py-8">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Main Content */}
          <div className="lg:col-span-2 space-y-6">
            {/* Header */}
            <div>
              <div className="flex items-center gap-2 mb-4">
                <Badge 
                  variant="secondary"
                  style={{ backgroundColor: `${prompt.category.color}20`, color: prompt.category.color }}
                >
                  {prompt.category.name}
                </Badge>
                {prompt.featured && (
                  <Badge variant="default" className="bg-yellow-500 hover:bg-yellow-600">
                    Featured
                  </Badge>
                )}
                <Badge variant="outline">v{prompt.version}</Badge>
              </div>
              
              <h1 className="text-3xl font-bold mb-4">{prompt.title}</h1>
              
              <div className="flex items-center gap-6 text-sm text-muted-foreground mb-6">
                <div className="flex items-center gap-2">
                  <Avatar className="h-8 w-8">
                    <AvatarImage src={`https://api.dicebear.com/7.x/initials/svg?seed=${prompt.user.name}`} />
                    <AvatarFallback className="text-xs">
                      {getUserInitials(prompt.user.name)}
                    </AvatarFallback>
                  </Avatar>
                  <Link 
                    href={`/users/${prompt.user.id}`}
                    className="hover:text-primary transition-colors"
                  >
                    {prompt.user.name}
                  </Link>
                </div>
                
                <div className="flex items-center gap-1">
                  <Calendar className="h-4 w-4" />
                  <span>{new Date(prompt.published_at).toLocaleDateString()}</span>
                </div>
                
                <div className="flex items-center gap-1">
                  <ShoppingCart className="h-4 w-4" />
                  <span>{prompt.purchase_count} sales</span>
                </div>
                
                {prompt.average_rating > 0 && (
                  <div className="flex items-center gap-1">
                    <RatingStars rating={prompt.average_rating} size="sm" />
                    <span>({prompt.reviews.length})</span>
                  </div>
                )}
              </div>
            </div>

            {/* Tabs */}
            <Tabs value={activeTab} onValueChange={setActiveTab}>
              <TabsList className="grid w-full grid-cols-3">
                <TabsTrigger value="description">Description</TabsTrigger>
                <TabsTrigger value="content">Content Preview</TabsTrigger>
                <TabsTrigger value="reviews">Reviews ({prompt.reviews.length})</TabsTrigger>
              </TabsList>
              
              <TabsContent value="description" className="mt-6">
                <Card>
                  <CardContent className="pt-6">
                    <div className="prose max-w-none">
                      <p className="text-lg leading-relaxed whitespace-pre-wrap">
                        {prompt.description}
                      </p>
                    </div>
                    
                    {prompt.tags.length > 0 && (
                      <div className="mt-6">
                        <h4 className="font-semibold mb-3">Tags</h4>
                        <div className="flex flex-wrap gap-2">
                          {prompt.tags.map((tag) => (
                            <TagBadge key={tag.id} tag={tag} interactive />
                          ))}
                        </div>
                      </div>
                    )}
                  </CardContent>
                </Card>
              </TabsContent>
              
              <TabsContent value="content" className="mt-6">
                <Card>
                  <CardContent className="pt-6">
                    {prompt.is_purchased ? (
                      <div className="space-y-4">
                        <div className="flex items-center gap-2 text-green-600">
                          <Shield className="h-5 w-5" />
                          <span className="font-medium">You own this prompt</span>
                        </div>
                        <div className="bg-muted p-4 rounded-lg">
                          <pre className="whitespace-pre-wrap text-sm">
                            {prompt.content}
                          </pre>
                        </div>
                        <Button className="w-full">
                          <Download className="h-4 w-4 mr-2" />
                          Download Prompt
                        </Button>
                      </div>
                    ) : (
                      <div className="text-center py-12">
                        <div className="mb-4">
                          <Eye className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                          <h3 className="text-lg font-semibold mb-2">Purchase to View Full Content</h3>
                          <p className="text-muted-foreground">
                            Get instant access to the complete prompt and start using it right away.
                          </p>
                        </div>
                        <Button onClick={handlePurchase} size="lg">
                          Purchase Now - {formatPrice(prompt.price, prompt.price_type, prompt.minimum_price)}
                        </Button>
                      </div>
                    )}
                  </CardContent>
                </Card>
              </TabsContent>
              
              <TabsContent value="reviews" className="mt-6">
                <div className="space-y-4">
                  {prompt.reviews.length > 0 ? (
                    prompt.reviews.map((review: any) => (
                      <Card key={review.id}>
                        <CardContent className="pt-6">
                          <div className="flex items-start gap-4">
                            <Avatar>
                              <AvatarImage src={`https://api.dicebear.com/7.x/initials/svg?seed=${review.user.name}`} />
                              <AvatarFallback>
                                {getUserInitials(review.user.name)}
                              </AvatarFallback>
                            </Avatar>
                            <div className="flex-1">
                              <div className="flex items-center gap-2 mb-2">
                                <span className="font-medium">{review.user.name}</span>
                                <RatingStars rating={review.rating} size="sm" />
                                <span className="text-sm text-muted-foreground">
                                  {new Date(review.created_at).toLocaleDateString()}
                                </span>
                              </div>
                              {review.title && (
                                <h4 className="font-medium mb-2">{review.title}</h4>
                              )}
                              <p className="text-muted-foreground">{review.review_text}</p>
                            </div>
                          </div>
                        </CardContent>
                      </Card>
                    ))
                  ) : (
                    <Card>
                      <CardContent className="pt-6 text-center py-12">
                        <MessageCircle className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                        <h3 className="text-lg font-semibold mb-2">No Reviews Yet</h3>
                        <p className="text-muted-foreground">
                          Be the first to review this prompt after purchasing it.
                        </p>
                      </CardContent>
                    </Card>
                  )}
                </div>
              </TabsContent>
            </Tabs>
          </div>

          {/* Sidebar */}
          <div className="space-y-6">
            {/* Purchase Card */}
            <Card>
              <CardHeader>
                <CardTitle className="text-2xl">
                  {formatPrice(prompt.price, prompt.price_type, prompt.minimum_price)}
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                {prompt.is_purchased ? (
                  <div className="space-y-3">
                    <div className="flex items-center gap-2 text-green-600">
                      <Shield className="h-5 w-5" />
                      <span className="font-medium">You own this prompt</span>
                    </div>
                    <Button className="w-full" size="lg">
                      <Download className="h-4 w-4 mr-2" />
                      Download
                    </Button>
                  </div>
                ) : (
                  <div className="space-y-3">
                    <Button onClick={handlePurchase} className="w-full" size="lg">
                      <ShoppingCart className="h-4 w-4 mr-2" />
                      Purchase Now
                    </Button>
                    <div className="flex gap-2">
                      <Button variant="outline" className="flex-1">
                        <Heart className="h-4 w-4 mr-2" />
                        Save
                      </Button>
                      <Button variant="outline" className="flex-1">
                        <Share2 className="h-4 w-4 mr-2" />
                        Share
                      </Button>
                    </div>
                  </div>
                )}
                
                <Separator />
                
                <div className="space-y-2 text-sm">
                  <div className="flex justify-between">
                    <span className="text-muted-foreground">Type:</span>
                    <span className="capitalize">{prompt.price_type.replace('_', ' ')}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-muted-foreground">Sales:</span>
                    <span>{prompt.purchase_count}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-muted-foreground">Version:</span>
                    <span>v{prompt.version}</span>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Author Card */}
            <Card>
              <CardHeader>
                <CardTitle className="text-lg">About the Creator</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="flex items-center gap-3 mb-4">
                  <Avatar className="h-12 w-12">
                    <AvatarImage src={`https://api.dicebear.com/7.x/initials/svg?seed=${prompt.user.name}`} />
                    <AvatarFallback>
                      {getUserInitials(prompt.user.name)}
                    </AvatarFallback>
                  </Avatar>
                  <div>
                    <h4 className="font-medium">{prompt.user.name}</h4>
                    <p className="text-sm text-muted-foreground">Prompt Creator</p>
                  </div>
                </div>
                <Button asChild variant="outline" className="w-full">
                  <Link href={`/users/${prompt.user.id}`}>
                    <User className="h-4 w-4 mr-2" />
                    View Profile
                  </Link>
                </Button>
              </CardContent>
            </Card>
          </div>
        </div>

        {/* Related Prompts */}
        {relatedPrompts.length > 0 && (
          <div className="mt-12">
            <h2 className="text-2xl font-bold mb-6">Related Prompts</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              {relatedPrompts.map((relatedPrompt) => (
                <div key={relatedPrompt.id} className="group">
                  <Link href={`/prompts/${relatedPrompt.uuid}`}>
                    <Card className="group-hover:shadow-lg transition-shadow">
                      <CardContent className="p-4">
                        <h3 className="font-medium mb-2 line-clamp-2 group-hover:text-primary transition-colors">
                          {relatedPrompt.title}
                        </h3>
                        <p className="text-sm text-muted-foreground mb-3 line-clamp-2">
                          {relatedPrompt.excerpt}
                        </p>
                        <div className="flex items-center justify-between">
                          <span className="font-semibold text-primary">
                            {formatPrice(relatedPrompt.price, relatedPrompt.price_type, relatedPrompt.minimum_price)}
                          </span>
                          {relatedPrompt.average_rating > 0 && (
                            <RatingStars rating={relatedPrompt.average_rating} size="sm" />
                          )}
                        </div>
                      </CardContent>
                    </Card>
                  </Link>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
    </AppLayout>
  );
}

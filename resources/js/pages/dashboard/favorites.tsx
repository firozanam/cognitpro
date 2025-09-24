import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { 
  Heart, 
  ShoppingBag, 
  FileText,
  Grid3X3,
  Star
} from 'lucide-react';

interface FavoritesProps {
  favorites: Array<{
    id: number;
    title: string;
    description: string;
    price: number;
    uuid: string;
    category: {
      id: number;
      name: string;
    };
    user: {
      id: number;
      name: string;
    };
    rating: number;
    review_count: number;
  }>;
}

export default function Favorites({ favorites }: FavoritesProps) {
  return (
    <AppLayout>
      <Head title="My Favorites - CognitPro" />

      <div className="container mx-auto px-4 py-8">
        {/* Header */}
        <div className="flex items-center justify-between mb-8">
          <div>
            <h1 className="text-3xl font-bold mb-2">My Favorites</h1>
            <p className="text-muted-foreground">
              Keep track of prompts you're interested in
            </p>
          </div>
          <Button asChild>
            <Link href="/prompts">
              <ShoppingBag className="h-4 w-4 mr-2" />
              Browse Prompts
            </Link>
          </Button>
        </div>

        {/* Favorites Summary */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Saved Prompts</CardTitle>
              <Heart className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{favorites.length}</div>
              <p className="text-xs text-muted-foreground">
                In your favorites
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Categories</CardTitle>
              <Grid3X3 className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                {new Set(favorites.map(f => f.category.name)).size}
              </div>
              <p className="text-xs text-muted-foreground">
                Different categories
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Avg Rating</CardTitle>
              <Star className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                {favorites.length > 0 
                  ? (favorites.reduce((sum, f) => sum + f.rating, 0) / favorites.length).toFixed(1)
                  : '0.0'
                }
              </div>
              <p className="text-xs text-muted-foreground">
                Of saved prompts
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Favorites List */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Heart className="h-5 w-5" />
              Your Favorite Prompts
            </CardTitle>
          </CardHeader>
          <CardContent>
            {favorites.length > 0 ? (
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {favorites.map((prompt) => (
                  <div key={prompt.id} className="border rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div className="flex items-start justify-between mb-3">
                      <div className="flex-1">
                        <Link 
                          href={`/prompts/${prompt.uuid}`}
                          className="font-semibold hover:underline line-clamp-2"
                        >
                          {prompt.title}
                        </Link>
                      </div>
                      <Button size="sm" variant="ghost" className="text-red-500 hover:text-red-600">
                        <Heart className="h-4 w-4 fill-current" />
                      </Button>
                    </div>
                    
                    <p className="text-sm text-muted-foreground mb-3 line-clamp-3">
                      {prompt.description}
                    </p>
                    
                    <div className="flex items-center justify-between text-sm mb-3">
                      <span className="text-muted-foreground">by {prompt.user.name}</span>
                      <span className="font-medium">${prompt.price}</span>
                    </div>
                    
                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-1">
                        <Star className="h-4 w-4 fill-current text-yellow-400" />
                        <span className="text-sm">{prompt.rating.toFixed(1)}</span>
                        <span className="text-xs text-muted-foreground">
                          ({prompt.review_count})
                        </span>
                      </div>
                      <Button size="sm" asChild>
                        <Link href={`/prompts/${prompt.uuid}`}>
                          View Details
                        </Link>
                      </Button>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <div className="text-center py-12">
                <Heart className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                <h3 className="text-lg font-medium mb-2">No favorites yet</h3>
                <p className="text-muted-foreground mb-6">
                  Start exploring prompts and save the ones you're interested in for later.
                </p>
                <div className="flex gap-4 justify-center">
                  <Button asChild>
                    <Link href="/prompts">
                      <ShoppingBag className="h-4 w-4 mr-2" />
                      Browse Prompts
                    </Link>
                  </Button>
                  <Button variant="outline" asChild>
                    <Link href="/categories">
                      <Grid3X3 className="h-4 w-4 mr-2" />
                      Explore Categories
                    </Link>
                  </Button>
                </div>
                
                <div className="mt-8 p-4 bg-muted/50 rounded-lg">
                  <h4 className="font-medium mb-2">How to save favorites:</h4>
                  <p className="text-sm text-muted-foreground">
                    Click the heart icon on any prompt to add it to your favorites list. 
                    This helps you keep track of prompts you want to purchase or reference later.
                  </p>
                </div>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}

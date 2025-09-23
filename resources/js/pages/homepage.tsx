import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import PromptCard from '@/components/prompt-card';
import CategoryCard from '@/components/category-card';
import { Search, TrendingUp, Star, Users, Zap } from 'lucide-react';

interface HomepageProps {
  featuredPrompts: any[];
  popularPrompts: any[];
  recentPrompts: any[];
  categories: any[];
  stats: {
    total_prompts: number;
    total_users: number;
    total_sales: number;
  };
}

export default function Homepage({ 
  featuredPrompts, 
  popularPrompts, 
  recentPrompts, 
  categories, 
  stats 
}: HomepageProps) {
  return (
    <AppLayout>
      <Head title="CognitPro - AI Prompt Marketplace" />

      {/* Hero Section */}
      <section className="bg-gradient-to-br from-primary/10 via-background to-secondary/10 py-20">
        <div className="container mx-auto px-4 text-center">
          <h1 className="text-4xl md:text-6xl font-bold mb-6 bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
            Discover Premium AI Prompts
          </h1>
          <p className="text-xl text-muted-foreground mb-8 max-w-2xl mx-auto">
            Unlock the power of AI with our curated collection of high-quality prompts. 
            Buy, sell, and discover prompts that deliver exceptional results.
          </p>
          
          {/* Search Bar */}
          <div className="max-w-2xl mx-auto mb-8">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-5 w-5" />
              <Input 
                placeholder="Search for prompts, categories, or creators..."
                className="pl-10 pr-4 py-3 text-lg"
              />
              <Button className="absolute right-2 top-1/2 transform -translate-y-1/2">
                Search
              </Button>
            </div>
          </div>

          {/* CTA Buttons */}
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Button asChild size="lg" className="text-lg px-8">
              <Link href="/prompts">Browse Prompts</Link>
            </Button>
            <Button asChild variant="outline" size="lg" className="text-lg px-8">
              <Link href="/sell">Start Selling</Link>
            </Button>
          </div>
        </div>
      </section>

      {/* Stats Section */}
      <section className="py-16 bg-muted/50">
        <div className="container mx-auto px-4">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            <Card className="text-center">
              <CardContent className="pt-6">
                <div className="flex items-center justify-center mb-4">
                  <Zap className="h-8 w-8 text-primary" />
                </div>
                <div className="text-3xl font-bold text-primary mb-2">
                  {stats.total_prompts.toLocaleString()}
                </div>
                <p className="text-muted-foreground">Quality Prompts</p>
              </CardContent>
            </Card>
            
            <Card className="text-center">
              <CardContent className="pt-6">
                <div className="flex items-center justify-center mb-4">
                  <Users className="h-8 w-8 text-primary" />
                </div>
                <div className="text-3xl font-bold text-primary mb-2">
                  {stats.total_users.toLocaleString()}
                </div>
                <p className="text-muted-foreground">Active Users</p>
              </CardContent>
            </Card>
            
            <Card className="text-center">
              <CardContent className="pt-6">
                <div className="flex items-center justify-center mb-4">
                  <TrendingUp className="h-8 w-8 text-primary" />
                </div>
                <div className="text-3xl font-bold text-primary mb-2">
                  {stats.total_sales.toLocaleString()}
                </div>
                <p className="text-muted-foreground">Successful Sales</p>
              </CardContent>
            </Card>
          </div>
        </div>
      </section>

      {/* Featured Prompts */}
      {featuredPrompts.length > 0 && (
        <section className="py-16">
          <div className="container mx-auto px-4">
            <div className="flex items-center justify-between mb-8">
              <div>
                <h2 className="text-3xl font-bold mb-2">Featured Prompts</h2>
                <p className="text-muted-foreground">Hand-picked prompts from our community</p>
              </div>
              <Button asChild variant="outline">
                <Link href="/prompts?featured=true">View All</Link>
              </Button>
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {featuredPrompts.slice(0, 6).map((prompt) => (
                <PromptCard key={prompt.id} prompt={prompt} />
              ))}
            </div>
          </div>
        </section>
      )}

      {/* Categories */}
      {categories.length > 0 && (
        <section className="py-16 bg-muted/50">
          <div className="container mx-auto px-4">
            <div className="flex items-center justify-between mb-8">
              <div>
                <h2 className="text-3xl font-bold mb-2">Browse Categories</h2>
                <p className="text-muted-foreground">Find prompts by category</p>
              </div>
              <Button asChild variant="outline">
                <Link href="/categories">View All</Link>
              </Button>
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {categories.slice(0, 6).map((category) => (
                <CategoryCard key={category.id} category={category} />
              ))}
            </div>
          </div>
        </section>
      )}

      {/* Popular Prompts */}
      {popularPrompts.length > 0 && (
        <section className="py-16">
          <div className="container mx-auto px-4">
            <div className="flex items-center justify-between mb-8">
              <div>
                <h2 className="text-3xl font-bold mb-2">Popular Prompts</h2>
                <p className="text-muted-foreground">Most purchased prompts this month</p>
              </div>
              <Button asChild variant="outline">
                <Link href="/prompts?sort_by=popularity">View All</Link>
              </Button>
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              {popularPrompts.slice(0, 4).map((prompt) => (
                <PromptCard key={prompt.id} prompt={prompt} />
              ))}
            </div>
          </div>
        </section>
      )}

      {/* Recent Prompts */}
      {recentPrompts.length > 0 && (
        <section className="py-16 bg-muted/50">
          <div className="container mx-auto px-4">
            <div className="flex items-center justify-between mb-8">
              <div>
                <h2 className="text-3xl font-bold mb-2">Latest Prompts</h2>
                <p className="text-muted-foreground">Fresh prompts from our creators</p>
              </div>
              <Button asChild variant="outline">
                <Link href="/prompts?sort_by=created_at">View All</Link>
              </Button>
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              {recentPrompts.slice(0, 4).map((prompt) => (
                <PromptCard key={prompt.id} prompt={prompt} />
              ))}
            </div>
          </div>
        </section>
      )}

      {/* CTA Section */}
      <section className="py-20 bg-gradient-to-r from-primary to-secondary text-primary-foreground">
        <div className="container mx-auto px-4 text-center">
          <h2 className="text-3xl md:text-4xl font-bold mb-4">
            Ready to Start Your AI Journey?
          </h2>
          <p className="text-xl mb-8 opacity-90 max-w-2xl mx-auto">
            Join thousands of creators and businesses using our platform to enhance their AI workflows.
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Button asChild size="lg" variant="secondary" className="text-lg px-8">
              <Link href="/register">Get Started</Link>
            </Button>
            <Button asChild size="lg" variant="outline" className="text-lg px-8 border-primary-foreground text-primary-foreground hover:bg-primary-foreground hover:text-primary">
              <Link href="/about">Learn More</Link>
            </Button>
          </div>
        </div>
      </section>
    </AppLayout>
  );
}

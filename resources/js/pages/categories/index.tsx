import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import CategoryCard from '@/components/category-card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowRight, Grid, Layers } from 'lucide-react';

interface Category {
  id: number;
  uuid: string;
  name: string;
  slug: string;
  description?: string;
  icon?: string;
  color: string;
  prompts_count: number;
  children?: Category[];
}

interface CategoriesIndexProps {
  categories: Category[];
}

export default function CategoriesIndex({ categories }: CategoriesIndexProps) {
  const totalPrompts = categories.reduce((sum, category) => sum + category.prompts_count, 0);
  const totalSubcategories = categories.reduce((sum, category) => sum + (category.children?.length || 0), 0);

  return (
    <AppLayout>
      <Head title="Browse Categories - CognitPro" />

      <div className="container mx-auto px-4 py-8">
        {/* Header */}
        <div className="text-center mb-12">
          <div className="flex items-center justify-center gap-2 mb-4">
            <Layers className="h-8 w-8 text-primary" />
            <h1 className="text-4xl font-bold">Browse Categories</h1>
          </div>
          <p className="text-xl text-muted-foreground max-w-2xl mx-auto mb-6">
            Discover AI prompts organized by category. Find exactly what you need for your projects.
          </p>
          
          {/* Stats */}
          <div className="flex items-center justify-center gap-8 text-sm text-muted-foreground">
            <div className="flex items-center gap-2">
              <Grid className="h-4 w-4" />
              <span>{categories.length} Categories</span>
            </div>
            <div className="flex items-center gap-2">
              <Layers className="h-4 w-4" />
              <span>{totalSubcategories} Subcategories</span>
            </div>
            <div className="flex items-center gap-2">
              <span>{totalPrompts.toLocaleString()} Prompts</span>
            </div>
          </div>
        </div>

        {/* Categories Grid */}
        {categories.length > 0 ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {categories.map((category) => (
              <CategoryCard 
                key={category.id} 
                category={category} 
                showPromptCount={true}
                showChildren={true}
                className="h-full"
              />
            ))}
          </div>
        ) : (
          <div className="text-center py-16">
            <Layers className="h-16 w-16 mx-auto text-muted-foreground mb-4" />
            <h3 className="text-xl font-semibold mb-2">No Categories Available</h3>
            <p className="text-muted-foreground mb-6">
              Categories are being set up. Check back soon!
            </p>
            <Button asChild>
              <Link href="/prompts">Browse All Prompts</Link>
            </Button>
          </div>
        )}

        {/* Call to Action */}
        {categories.length > 0 && (
          <div className="mt-16 text-center">
            <div className="bg-muted/50 rounded-lg p-8">
              <h2 className="text-2xl font-bold mb-4">Can't find what you're looking for?</h2>
              <p className="text-muted-foreground mb-6 max-w-2xl mx-auto">
                Browse all prompts or use our advanced search to find exactly what you need.
              </p>
              <div className="flex items-center justify-center gap-4">
                <Button asChild>
                  <Link href="/prompts">
                    Browse All Prompts
                    <ArrowRight className="h-4 w-4 ml-2" />
                  </Link>
                </Button>
                <Button asChild variant="outline">
                  <Link href="/prompts?search=">Advanced Search</Link>
                </Button>
              </div>
            </div>
          </div>
        )}
      </div>
    </AppLayout>
  );
}

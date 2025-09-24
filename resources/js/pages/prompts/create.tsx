import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
  ArrowLeft,
  Save,
  Eye,
  FileText,
  DollarSign,
  Layers,
  AlertCircle,
  CheckCircle,
  X
} from 'lucide-react';

interface Category {
  id: number;
  name: string;
  slug: string;
  color: string;
}

interface Tag {
  id: number;
  name: string;
  slug: string;
}

interface CreatePromptProps {
  categories: Category[];
  tags: Tag[];
}

export default function CreatePrompt({ categories, tags }: CreatePromptProps) {
  const [selectedTags, setSelectedTags] = useState<number[]>([]);
  const [showPreview, setShowPreview] = useState(false);

  const { data, setData, post, processing, errors, reset } = useForm({
    title: '',
    description: '',
    content: '',
    category_id: '',
    price_type: 'fixed',
    price: '',
    minimum_price: '5.00',
    tags: [] as number[],
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    const formData = {
      ...data,
      tags: selectedTags,
      price: data.price_type === 'free' ? null : data.price,
      minimum_price: data.price_type === 'pay_what_you_want' ? data.minimum_price : null,
    };

    post('/api/prompts', {
      onSuccess: () => {
        // Redirect will be handled by Inertia
      },
    });
  };

  const handleSaveDraft = () => {
    const formData = {
      ...data,
      tags: selectedTags,
      status: 'draft',
      price: data.price_type === 'free' ? null : data.price,
      minimum_price: data.price_type === 'pay_what_you_want' ? data.minimum_price : null,
    };

    post('/api/prompts', {
      onSuccess: () => {
        // Redirect will be handled by Inertia
      },
    });
  };

  const handleTagToggle = (tagId: number) => {
    setSelectedTags(prev => {
      const newTags = prev.includes(tagId) 
        ? prev.filter(id => id !== tagId)
        : [...prev, tagId];
      setData('tags', newTags);
      return newTags;
    });
  };

  const selectedCategory = categories.find(cat => cat.id === parseInt(data.category_id));

  return (
    <AppLayout>
      <Head title="Create New Prompt - CognitPro" />

      <div className="container mx-auto px-4 py-8 max-w-4xl">
        {/* Header */}
        <div className="flex items-center justify-between mb-8">
          <div className="flex items-center gap-4">
            <Button asChild variant="outline" size="sm">
              <Link href="/dashboard">
                <ArrowLeft className="h-4 w-4 mr-2" />
                Back to Dashboard
              </Link>
            </Button>
            <div>
              <h1 className="text-3xl font-bold">Create New Prompt</h1>
              <p className="text-muted-foreground">
                Share your AI prompt with the community
              </p>
            </div>
          </div>
          
          <div className="flex items-center gap-2">
            <Button
              type="button"
              variant="outline"
              onClick={() => setShowPreview(!showPreview)}
            >
              <Eye className="h-4 w-4 mr-2" />
              {showPreview ? 'Hide Preview' : 'Preview'}
            </Button>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Main Form */}
          <div className="lg:col-span-2">
            <form onSubmit={handleSubmit} className="space-y-6">
              {/* Basic Information */}
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <FileText className="h-5 w-5" />
                    Basic Information
                  </CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div>
                    <Label htmlFor="title">Title *</Label>
                    <Input
                      id="title"
                      value={data.title}
                      onChange={(e) => setData('title', e.target.value)}
                      placeholder="Enter a compelling title for your prompt"
                      className={errors.title ? 'border-destructive' : ''}
                    />
                    {errors.title && (
                      <p className="text-sm text-destructive mt-1">{errors.title}</p>
                    )}
                  </div>

                  <div>
                    <Label htmlFor="description">Description *</Label>
                    <Textarea
                      id="description"
                      value={data.description}
                      onChange={(e) => setData('description', e.target.value)}
                      placeholder="Describe what your prompt does and how to use it"
                      rows={4}
                      className={errors.description ? 'border-destructive' : ''}
                    />
                    {errors.description && (
                      <p className="text-sm text-destructive mt-1">{errors.description}</p>
                    )}
                  </div>

                  <div>
                    <Label htmlFor="content">Prompt Content *</Label>
                    <Textarea
                      id="content"
                      value={data.content}
                      onChange={(e) => setData('content', e.target.value)}
                      placeholder="Enter your complete prompt here..."
                      rows={8}
                      className={errors.content ? 'border-destructive' : ''}
                    />
                    {errors.content && (
                      <p className="text-sm text-destructive mt-1">{errors.content}</p>
                    )}
                    <p className="text-sm text-muted-foreground mt-1">
                      This is the actual prompt that buyers will receive
                    </p>
                  </div>
                </CardContent>
              </Card>

              {/* Category and Tags */}
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <Layers className="h-5 w-5" />
                    Category & Tags
                  </CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div>
                    <Label htmlFor="category">Category *</Label>
                    <Select value={data.category_id} onValueChange={(value) => setData('category_id', value)}>
                      <SelectTrigger className={errors.category_id ? 'border-destructive' : ''}>
                        <SelectValue placeholder="Select a category" />
                      </SelectTrigger>
                      <SelectContent>
                        {categories.map((category) => (
                          <SelectItem key={category.id} value={category.id.toString()}>
                            <div className="flex items-center gap-2">
                              <div 
                                className="w-3 h-3 rounded-full"
                                style={{ backgroundColor: category.color }}
                              />
                              {category.name}
                            </div>
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    {errors.category_id && (
                      <p className="text-sm text-destructive mt-1">{errors.category_id}</p>
                    )}
                  </div>

                  <div>
                    <Label>Tags (Optional)</Label>
                    <div className="flex flex-wrap gap-2 mt-2">
                      {tags.map((tag) => (
                        <Badge
                          key={tag.id}
                          variant={selectedTags.includes(tag.id) ? 'default' : 'outline'}
                          className="cursor-pointer"
                          onClick={() => handleTagToggle(tag.id)}
                        >
                          {tag.name}
                          {selectedTags.includes(tag.id) && (
                            <X className="h-3 w-3 ml-1" />
                          )}
                        </Badge>
                      ))}
                    </div>
                    <p className="text-sm text-muted-foreground mt-1">
                      Click tags to add them to your prompt
                    </p>
                  </div>
                </CardContent>
              </Card>

              {/* Pricing */}
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <DollarSign className="h-5 w-5" />
                    Pricing
                  </CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div>
                    <Label>Price Type *</Label>
                    <Select value={data.price_type} onValueChange={(value) => setData('price_type', value)}>
                      <SelectTrigger>
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="free">Free</SelectItem>
                        <SelectItem value="fixed">Fixed Price</SelectItem>
                        <SelectItem value="pay_what_you_want">Pay What You Want</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  {data.price_type === 'fixed' && (
                    <div>
                      <Label htmlFor="price">Price (USD) *</Label>
                      <Input
                        id="price"
                        type="number"
                        step="0.01"
                        min="0"
                        value={data.price}
                        onChange={(e) => setData('price', e.target.value)}
                        placeholder="9.99"
                        className={errors.price ? 'border-destructive' : ''}
                      />
                      {errors.price && (
                        <p className="text-sm text-destructive mt-1">{errors.price}</p>
                      )}
                    </div>
                  )}

                  {data.price_type === 'pay_what_you_want' && (
                    <div>
                      <Label htmlFor="minimum_price">Minimum Price (USD) *</Label>
                      <Input
                        id="minimum_price"
                        type="number"
                        step="0.01"
                        min="0"
                        value={data.minimum_price}
                        onChange={(e) => setData('minimum_price', e.target.value)}
                        placeholder="5.00"
                        className={errors.minimum_price ? 'border-destructive' : ''}
                      />
                      {errors.minimum_price && (
                        <p className="text-sm text-destructive mt-1">{errors.minimum_price}</p>
                      )}
                    </div>
                  )}
                </CardContent>
              </Card>

              {/* Actions */}
              <div className="flex items-center justify-between">
                <Button
                  type="button"
                  variant="outline"
                  onClick={handleSaveDraft}
                  disabled={processing}
                >
                  <Save className="h-4 w-4 mr-2" />
                  Save as Draft
                </Button>

                <Button type="submit" disabled={processing}>
                  <CheckCircle className="h-4 w-4 mr-2" />
                  {processing ? 'Creating...' : 'Create Prompt'}
                </Button>
              </div>
            </form>
          </div>

          {/* Sidebar */}
          <div className="space-y-6">
            {/* Preview */}
            {showPreview && (
              <Card>
                <CardHeader>
                  <CardTitle>Preview</CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                  {data.title && (
                    <div>
                      <h3 className="font-semibold">{data.title}</h3>
                    </div>
                  )}
                  
                  {selectedCategory && (
                    <Badge 
                      style={{ 
                        backgroundColor: `${selectedCategory.color}20`, 
                        color: selectedCategory.color 
                      }}
                    >
                      {selectedCategory.name}
                    </Badge>
                  )}

                  {data.description && (
                    <p className="text-sm text-muted-foreground">{data.description}</p>
                  )}

                  {data.price_type !== 'free' && (
                    <div className="text-lg font-semibold">
                      {data.price_type === 'fixed' && data.price && `$${data.price}`}
                      {data.price_type === 'pay_what_you_want' && data.minimum_price && `$${data.minimum_price}+`}
                    </div>
                  )}

                  {selectedTags.length > 0 && (
                    <div className="flex flex-wrap gap-1">
                      {selectedTags.map(tagId => {
                        const tag = tags.find(t => t.id === tagId);
                        return tag ? (
                          <Badge key={tag.id} variant="outline" className="text-xs">
                            {tag.name}
                          </Badge>
                        ) : null;
                      })}
                    </div>
                  )}
                </CardContent>
              </Card>
            )}

            {/* Tips */}
            <Card>
              <CardHeader>
                <CardTitle>Tips for Success</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3 text-sm">
                <div className="flex items-start gap-2">
                  <AlertCircle className="h-4 w-4 text-primary mt-0.5 flex-shrink-0" />
                  <p>Write a clear, descriptive title that explains what your prompt does</p>
                </div>
                <div className="flex items-start gap-2">
                  <AlertCircle className="h-4 w-4 text-primary mt-0.5 flex-shrink-0" />
                  <p>Include examples or use cases in your description</p>
                </div>
                <div className="flex items-start gap-2">
                  <AlertCircle className="h-4 w-4 text-primary mt-0.5 flex-shrink-0" />
                  <p>Choose the most relevant category and tags</p>
                </div>
                <div className="flex items-start gap-2">
                  <AlertCircle className="h-4 w-4 text-primary mt-0.5 flex-shrink-0" />
                  <p>Test your prompt before publishing</p>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}

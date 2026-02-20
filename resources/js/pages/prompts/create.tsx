import { Head, Link, router } from '@inertiajs/react';
import { useState, FormEvent } from 'react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { 
    ArrowLeft, 
    Save, 
    Eye, 
    Plus, 
    X, 
    DollarSign,
    Sparkles,
    FileText,
    Tag as TagIcon
} from 'lucide-react';
import { type BreadcrumbItem } from '@/types';

interface Category {
    id: number;
    name: string;
    slug: string;
}

interface AiModel {
    value: string;
    label: string;
}

interface Props {
    categories: Category[];
    aiModels: AiModel[];
}

export default function PromptCreate({ categories, aiModels }: Props) {
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [showPreview, setShowPreview] = useState(false);
    const [tagInput, setTagInput] = useState('');
    
    const [formData, setFormData] = useState({
        title: '',
        description: '',
        content: '',
        category_id: '',
        ai_model: '',
        pricing_model: 'fixed',
        price: '9.99',
        min_price: '',
        tags: [] as string[],
    });

    const [errors, setErrors] = useState<Record<string, string>>({});

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Seller Dashboard',
            href: '/seller/dashboard',
        },
        {
            title: 'Create Prompt',
            href: '/prompts/create',
        },
    ];

    const handleChange = (field: string, value: string) => {
        setFormData(prev => ({ ...prev, [field]: value }));
        // Clear error when field is modified
        if (errors[field]) {
            setErrors(prev => {
                const newErrors = { ...prev };
                delete newErrors[field];
                return newErrors;
            });
        }
    };

    const handleAddTag = () => {
        const tag = tagInput.trim().toLowerCase();
        if (tag && !formData.tags.includes(tag) && formData.tags.length < 10) {
            setFormData(prev => ({ ...prev, tags: [...prev.tags, tag] }));
            setTagInput('');
        }
    };

    const handleRemoveTag = (tagToRemove: string) => {
        setFormData(prev => ({
            ...prev,
            tags: prev.tags.filter(tag => tag !== tagToRemove)
        }));
    };

    const handleTagKeyDown = (e: React.KeyboardEvent) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleAddTag();
        }
    };

    const handleSubmit = (e: FormEvent, asDraft: boolean = false) => {
        e.preventDefault();
        setIsSubmitting(true);
        setErrors({});

        const submitData = {
            ...formData,
            price: formData.pricing_model === 'free' ? 0 : parseFloat(formData.price),
            min_price: formData.pricing_model === 'pay_what_you_want' ? parseFloat(formData.min_price) : null,
            status: asDraft ? 'draft' : 'pending',
        };

        router.post('/prompts', submitData, {
            onSuccess: () => {
                setIsSubmitting(false);
            },
            onError: (errors) => {
                setErrors(errors as Record<string, string>);
                setIsSubmitting(false);
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Prompt" />
            
            <div className="container py-8 max-w-4xl">
                {/* Header */}
                <div className="flex items-center justify-between mb-8">
                    <div>
                        <Link
                            href="/seller/dashboard"
                            className="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-2"
                        >
                            <ArrowLeft className="h-4 w-4 mr-1" />
                            Back to Dashboard
                        </Link>
                        <h1 className="text-3xl font-bold">Create New Prompt</h1>
                        <p className="text-muted-foreground mt-1">
                            Share your prompt with the community and start earning
                        </p>
                    </div>
                    <Button
                        variant="outline"
                        onClick={() => setShowPreview(!showPreview)}
                    >
                        <Eye className="h-4 w-4 mr-2" />
                        {showPreview ? 'Hide Preview' : 'Preview'}
                    </Button>
                </div>

                <form onSubmit={(e) => handleSubmit(e, true)} className="space-y-8">
                    {/* Basic Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <FileText className="h-5 w-5" />
                                Basic Information
                            </CardTitle>
                            <CardDescription>
                                Provide the essential details about your prompt
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            {/* Title */}
                            <div className="space-y-2">
                                <Label htmlFor="title">Title *</Label>
                                <Input
                                    id="title"
                                    placeholder="e.g., Professional Email Writer for Business Communication"
                                    value={formData.title}
                                    onChange={(e) => handleChange('title', e.target.value)}
                                    className={errors.title ? 'border-destructive' : ''}
                                />
                                {errors.title && (
                                    <p className="text-sm text-destructive">{errors.title}</p>
                                )}
                                <p className="text-xs text-muted-foreground">
                                    A clear, descriptive title that helps buyers understand what your prompt does
                                </p>
                            </div>

                            {/* Description */}
                            <div className="space-y-2">
                                <Label htmlFor="description">Description *</Label>
                                <Textarea
                                    id="description"
                                    placeholder="Describe what your prompt does, what use cases it's best for, and any tips for getting the best results..."
                                    rows={5}
                                    value={formData.description}
                                    onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => handleChange('description', e.target.value)}
                                    className={errors.description ? 'border-destructive' : ''}
                                />
                                {errors.description && (
                                    <p className="text-sm text-destructive">{errors.description}</p>
                                )}
                            </div>

                            {/* Category and AI Model */}
                            <div className="grid gap-6 sm:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="category">Category *</Label>
                                    <Select
                                        value={formData.category_id}
                                        onValueChange={(value) => handleChange('category_id', value)}
                                    >
                                        <SelectTrigger className={errors.category_id ? 'border-destructive' : ''}>
                                            <SelectValue placeholder="Select a category" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {categories.map((category) => (
                                                <SelectItem key={category.id} value={category.id.toString()}>
                                                    {category.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.category_id && (
                                        <p className="text-sm text-destructive">{errors.category_id}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="ai_model">AI Model *</Label>
                                    <Select
                                        value={formData.ai_model}
                                        onValueChange={(value) => handleChange('ai_model', value)}
                                    >
                                        <SelectTrigger className={errors.ai_model ? 'border-destructive' : ''}>
                                            <SelectValue placeholder="Select an AI model" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {aiModels.map((model) => (
                                                <SelectItem key={model.value} value={model.value}>
                                                    {model.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.ai_model && (
                                        <p className="text-sm text-destructive">{errors.ai_model}</p>
                                    )}
                                </div>
                            </div>

                            {/* Tags */}
                            <div className="space-y-2">
                                <Label htmlFor="tags">Tags</Label>
                                <div className="flex gap-2">
                                    <Input
                                        id="tags"
                                        placeholder="Add tags (press Enter)"
                                        value={tagInput}
                                        onChange={(e) => setTagInput(e.target.value)}
                                        onKeyDown={handleTagKeyDown}
                                    />
                                    <Button type="button" variant="outline" onClick={handleAddTag}>
                                        <Plus className="h-4 w-4" />
                                    </Button>
                                </div>
                                {formData.tags.length > 0 && (
                                    <div className="flex flex-wrap gap-2 mt-2">
                                        {formData.tags.map((tag) => (
                                            <Badge key={tag} variant="secondary" className="gap-1">
                                                <TagIcon className="h-3 w-3" />
                                                {tag}
                                                <button
                                                    type="button"
                                                    onClick={() => handleRemoveTag(tag)}
                                                    className="ml-1 hover:text-destructive"
                                                >
                                                    <X className="h-3 w-3" />
                                                </button>
                                            </Badge>
                                        ))}
                                    </div>
                                )}
                                <p className="text-xs text-muted-foreground">
                                    Add up to 10 tags to help buyers find your prompt
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Prompt Content */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Sparkles className="h-5 w-5" />
                                Prompt Content
                            </CardTitle>
                            <CardDescription>
                                The actual prompt that buyers will receive
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-2">
                                <Label htmlFor="content">Prompt *</Label>
                                <Textarea
                                    id="content"
                                    placeholder="Enter your prompt here. This is what buyers will copy and use..."
                                    rows={12}
                                    value={formData.content}
                                    onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => handleChange('content', e.target.value)}
                                    className={`font-mono text-sm ${errors.content ? 'border-destructive' : ''}`}
                                />
                                {errors.content && (
                                    <p className="text-sm text-destructive">{errors.content}</p>
                                )}
                                <p className="text-xs text-muted-foreground">
                                    Use clear instructions and placeholders like {'{{variable}}'} for customizable parts
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
                            <CardDescription>
                                Set your price and pricing model
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            {/* Pricing Model */}
                            <div className="space-y-2">
                                <Label>Pricing Model *</Label>
                                <Select
                                    value={formData.pricing_model}
                                    onValueChange={(value) => handleChange('pricing_model', value)}
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="fixed">Fixed Price</SelectItem>
                                        <SelectItem value="pay_what_you_want">Pay What You Want</SelectItem>
                                        <SelectItem value="free">Free</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            {/* Price */}
                            {formData.pricing_model !== 'free' && (
                                <div className="grid gap-6 sm:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="price">
                                            {formData.pricing_model === 'fixed' ? 'Price *' : 'Suggested Price'}
                                        </Label>
                                        <div className="relative">
                                            <DollarSign className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                            <Input
                                                id="price"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                max="9999.99"
                                                placeholder="9.99"
                                                value={formData.price}
                                                onChange={(e) => handleChange('price', e.target.value)}
                                                className={`pl-9 ${errors.price ? 'border-destructive' : ''}`}
                                            />
                                        </div>
                                        {errors.price && (
                                            <p className="text-sm text-destructive">{errors.price}</p>
                                        )}
                                    </div>

                                    {formData.pricing_model === 'pay_what_you_want' && (
                                        <div className="space-y-2">
                                            <Label htmlFor="min_price">Minimum Price *</Label>
                                            <div className="relative">
                                                <DollarSign className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                                <Input
                                                    id="min_price"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    max="9999.99"
                                                    placeholder="1.00"
                                                    value={formData.min_price}
                                                    onChange={(e) => handleChange('min_price', e.target.value)}
                                                    className={`pl-9 ${errors.min_price ? 'border-destructive' : ''}`}
                                                />
                                            </div>
                                            {errors.min_price && (
                                                <p className="text-sm text-destructive">{errors.min_price}</p>
                                            )}
                                        </div>
                                    )}
                                </div>
                            )}

                            {formData.pricing_model === 'free' && (
                                <p className="text-sm text-muted-foreground">
                                    Free prompts are a great way to build your reputation and attract buyers to your paid content.
                                </p>
                            )}
                        </CardContent>
                    </Card>

                    {/* Preview */}
                    {showPreview && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Preview</CardTitle>
                                <CardDescription>
                                    How your prompt will appear in the marketplace
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    <div>
                                        <h3 className="text-xl font-semibold">
                                            {formData.title || 'Untitled Prompt'}
                                        </h3>
                                        <p className="text-muted-foreground mt-2">
                                            {formData.description || 'No description provided'}
                                        </p>
                                    </div>
                                    <div className="flex flex-wrap gap-2">
                                        {formData.ai_model && (
                                            <Badge variant="outline">{formData.ai_model}</Badge>
                                        )}
                                        {formData.category_id && (
                                            <Badge variant="secondary">
                                                {categories.find(c => c.id.toString() === formData.category_id)?.name}
                                            </Badge>
                                        )}
                                    </div>
                                    <div className="text-lg font-bold">
                                        {formData.pricing_model === 'free' 
                                            ? 'Free' 
                                            : `$${parseFloat(formData.price || '0').toFixed(2)}`
                                        }
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {/* Actions */}
                    <div className="flex items-center justify-end gap-4">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={(e) => handleSubmit(e, true)}
                            disabled={isSubmitting}
                        >
                            <Save className="h-4 w-4 mr-2" />
                            Save as Draft
                        </Button>
                        <Button
                            type="submit"
                            disabled={isSubmitting}
                        >
                            {isSubmitting ? 'Submitting...' : 'Submit for Review'}
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}

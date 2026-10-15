import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import {
    AlertCircle,
    ArrowLeft,
    Plus,
    Save,
    Tag as TagIcon,
    X,
} from 'lucide-react';
import { FormEvent, useState } from 'react';

interface Tag {
    id: number;
    name: string;
    slug: string;
}

interface Prompt {
    id: number;
    title: string;
    slug: string;
    description: string;
    content: string;
    ai_model: string;
    category_id: number;
    pricing_model: string;
    price: string | number;
    min_price: string | number | null;
    status: string;
    rejection_reason: string | null;
    tags: Tag[];
}

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
    prompt: Prompt;
    categories: Category[];
    aiModels: AiModel[];
}

export default function PromptEdit({ prompt, categories, aiModels }: Props) {
    const [formData, setFormData] = useState({
        title: prompt.title,
        description: prompt.description,
        content: prompt.content,
        category_id: prompt.category_id.toString(),
        ai_model: prompt.ai_model,
        pricing_model: prompt.pricing_model,
        price: prompt.price?.toString() ?? '',
        min_price: prompt.min_price?.toString() ?? '',
        tags: prompt.tags?.map((tag) => tag.name) ?? [],
    });
    const [tagInput, setTagInput] = useState('');
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [isSubmitting, setIsSubmitting] = useState(false);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Seller Dashboard', href: '/seller/sales' },
        { title: 'Edit Prompt' },
    ];

    const handleChange = (field: string, value: string) => {
        setFormData((prev) => ({ ...prev, [field]: value }));
        if (errors[field]) {
            setErrors((prev) => {
                const next = { ...prev };
                delete next[field];
                return next;
            });
        }
    };

    const handleAddTag = () => {
        const tag = tagInput.trim().toLowerCase();
        if (tag && !formData.tags.includes(tag) && formData.tags.length < 10) {
            setFormData((prev) => ({ ...prev, tags: [...prev.tags, tag] }));
            setTagInput('');
        }
    };

    const handleRemoveTag = (tagToRemove: string) => {
        setFormData((prev) => ({
            ...prev,
            tags: prev.tags.filter((tag) => tag !== tagToRemove),
        }));
    };

    const handleTagKeyDown = (e: React.KeyboardEvent) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleAddTag();
        }
    };

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);
        setErrors({});

        const submitData = {
            ...formData,
            price:
                formData.pricing_model === 'free'
                    ? 0
                    : parseFloat(formData.price),
            min_price:
                formData.pricing_model === 'pay_what_you_want'
                    ? parseFloat(formData.min_price)
                    : null,
        };

        router.put(`/prompts/${prompt.id}`, submitData, {
            onSuccess: () => setIsSubmitting(false),
            onError: (errs) => {
                setErrors(errs as Record<string, string>);
                setIsSubmitting(false);
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Prompt" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div>
                    <Link
                        href={`/prompts/${prompt.slug}`}
                        className="mb-2 inline-flex items-center text-sm text-muted-foreground hover:text-foreground"
                    >
                        <ArrowLeft className="mr-1 h-4 w-4" />
                        Back to prompt
                    </Link>
                    <div className="flex items-center gap-3">
                        <h1 className="text-2xl font-bold tracking-tight">
                            Edit Prompt
                        </h1>
                        <Badge
                            variant={
                                prompt.status === 'approved'
                                    ? 'default'
                                    : 'secondary'
                            }
                        >
                            {prompt.status}
                        </Badge>
                    </div>
                </div>

                {prompt.status === 'rejected' && prompt.rejection_reason && (
                    <Alert variant="destructive">
                        <AlertCircle className="h-4 w-4" />
                        <AlertTitle>Your prompt was rejected</AlertTitle>
                        <AlertDescription>
                            {prompt.rejection_reason}
                        </AlertDescription>
                    </Alert>
                )}

                <form onSubmit={handleSubmit} className="space-y-8">
                    {/* Basic Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                            <CardDescription>
                                Update the essential details about your prompt
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            {/* Title */}
                            <div className="space-y-2">
                                <Label htmlFor="title">Title *</Label>
                                <Input
                                    id="title"
                                    value={formData.title}
                                    onChange={(e) =>
                                        handleChange('title', e.target.value)
                                    }
                                    className={
                                        errors.title ? 'border-destructive' : ''
                                    }
                                />
                                {errors.title && (
                                    <p className="text-sm text-destructive">
                                        {errors.title}
                                    </p>
                                )}
                            </div>

                            {/* Description */}
                            <div className="space-y-2">
                                <Label htmlFor="description">
                                    Description *
                                </Label>
                                <Textarea
                                    id="description"
                                    rows={4}
                                    value={formData.description}
                                    onChange={(e) =>
                                        handleChange(
                                            'description',
                                            e.target.value,
                                        )
                                    }
                                    className={
                                        errors.description
                                            ? 'border-destructive'
                                            : ''
                                    }
                                />
                                {errors.description && (
                                    <p className="text-sm text-destructive">
                                        {errors.description}
                                    </p>
                                )}
                            </div>

                            <div className="grid gap-6 md:grid-cols-2">
                                {/* Category */}
                                <div className="space-y-2">
                                    <Label>Category *</Label>
                                    <Select
                                        value={formData.category_id}
                                        onValueChange={(value) =>
                                            handleChange('category_id', value)
                                        }
                                    >
                                        <SelectTrigger
                                            className={
                                                errors.category_id
                                                    ? 'border-destructive'
                                                    : ''
                                            }
                                        >
                                            <SelectValue placeholder="Select a category" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {categories.map((category) => (
                                                <SelectItem
                                                    key={category.id}
                                                    value={category.id.toString()}
                                                >
                                                    {category.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.category_id && (
                                        <p className="text-sm text-destructive">
                                            {errors.category_id}
                                        </p>
                                    )}
                                </div>

                                {/* AI Model */}
                                <div className="space-y-2">
                                    <Label>AI Model *</Label>
                                    <Select
                                        value={formData.ai_model}
                                        onValueChange={(value) =>
                                            handleChange('ai_model', value)
                                        }
                                    >
                                        <SelectTrigger
                                            className={
                                                errors.ai_model
                                                    ? 'border-destructive'
                                                    : ''
                                            }
                                        >
                                            <SelectValue placeholder="Select an AI model" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {aiModels.map((model) => (
                                                <SelectItem
                                                    key={model.value}
                                                    value={model.value}
                                                >
                                                    {model.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.ai_model && (
                                        <p className="text-sm text-destructive">
                                            {errors.ai_model}
                                        </p>
                                    )}
                                </div>
                            </div>

                            {/* Tags */}
                            <div className="space-y-2">
                                <Label htmlFor="tag-input">Tags</Label>
                                <div className="flex gap-2">
                                    <Input
                                        id="tag-input"
                                        placeholder="Add a tag and press Enter"
                                        value={tagInput}
                                        onChange={(e) =>
                                            setTagInput(e.target.value)
                                        }
                                        onKeyDown={handleTagKeyDown}
                                    />
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={handleAddTag}
                                    >
                                        <Plus className="h-4 w-4" />
                                    </Button>
                                </div>
                                {formData.tags.length > 0 && (
                                    <div className="flex flex-wrap gap-2 pt-1">
                                        {formData.tags.map((tag) => (
                                            <Badge
                                                key={tag}
                                                variant="secondary"
                                                className="gap-1"
                                            >
                                                <TagIcon className="h-3 w-3" />
                                                {tag}
                                                <button
                                                    type="button"
                                                    aria-label={`Remove tag ${tag}`}
                                                    onClick={() =>
                                                        handleRemoveTag(tag)
                                                    }
                                                    className="ml-0.5 rounded-full hover:text-destructive"
                                                >
                                                    <X className="h-3 w-3" />
                                                </button>
                                            </Badge>
                                        ))}
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Prompt Content */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Prompt Content *</CardTitle>
                            <CardDescription>
                                The actual prompt text buyers receive after
                                purchase
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-2">
                            <Textarea
                                id="content"
                                rows={12}
                                value={formData.content}
                                onChange={(e) =>
                                    handleChange('content', e.target.value)
                                }
                                className={`font-mono text-sm ${errors.content ? 'border-destructive' : ''}`}
                            />
                            {errors.content && (
                                <p className="text-sm text-destructive">
                                    {errors.content}
                                </p>
                            )}
                        </CardContent>
                    </Card>

                    {/* Pricing */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Pricing</CardTitle>
                            <CardDescription>
                                How buyers pay for this prompt
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            <div className="grid gap-6 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label>Pricing model *</Label>
                                    <Select
                                        value={formData.pricing_model}
                                        onValueChange={(value) =>
                                            handleChange('pricing_model', value)
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="fixed">
                                                Fixed price
                                            </SelectItem>
                                            <SelectItem value="pay_what_you_want">
                                                Pay what you want
                                            </SelectItem>
                                            <SelectItem value="free">
                                                Free
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                {formData.pricing_model === 'fixed' && (
                                    <div className="space-y-2">
                                        <Label htmlFor="price">
                                            Price (USD) *
                                        </Label>
                                        <Input
                                            id="price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            value={formData.price}
                                            onChange={(e) =>
                                                handleChange(
                                                    'price',
                                                    e.target.value,
                                                )
                                            }
                                            className={
                                                errors.price
                                                    ? 'border-destructive'
                                                    : ''
                                            }
                                        />
                                        {errors.price && (
                                            <p className="text-sm text-destructive">
                                                {errors.price}
                                            </p>
                                        )}
                                    </div>
                                )}

                                {formData.pricing_model ===
                                    'pay_what_you_want' && (
                                    <div className="space-y-2">
                                        <Label htmlFor="min_price">
                                            Minimum price (USD) *
                                        </Label>
                                        <Input
                                            id="min_price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            value={formData.min_price}
                                            onChange={(e) =>
                                                handleChange(
                                                    'min_price',
                                                    e.target.value,
                                                )
                                            }
                                            className={
                                                errors.min_price
                                                    ? 'border-destructive'
                                                    : ''
                                            }
                                        />
                                        {errors.min_price && (
                                            <p className="text-sm text-destructive">
                                                {errors.min_price}
                                            </p>
                                        )}
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Actions */}
                    <div className="flex items-center justify-end gap-4">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => window.history.back()}
                            disabled={isSubmitting}
                        >
                            Cancel
                        </Button>
                        <Button type="submit" disabled={isSubmitting}>
                            <Save className="mr-2 h-4 w-4" />
                            {isSubmitting ? 'Saving…' : 'Save Changes'}
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}

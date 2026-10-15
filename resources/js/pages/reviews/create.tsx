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
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Star } from 'lucide-react';
import { FormEvent, useState } from 'react';

interface Purchase {
    id: number;
    prompt: {
        id: number;
        title: string;
        slug: string;
        seller: { id: number; name: string };
    };
}

interface Props {
    purchase: Purchase;
}

export default function ReviewCreate({ purchase }: Props) {
    const [rating, setRating] = useState(0);
    const [hoveredRating, setHoveredRating] = useState(0);
    const [title, setTitle] = useState('');
    const [content, setContent] = useState('');
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [isSubmitting, setIsSubmitting] = useState(false);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'My Purchases', href: '/purchases' },
        { title: 'Write a Review' },
    ];

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);
        setErrors({});

        router.post(
            `/purchases/${purchase.id}/review`,
            { rating, title, content },
            {
                onSuccess: () => setIsSubmitting(false),
                onError: (errs) => {
                    setErrors(errs as Record<string, string>);
                    setIsSubmitting(false);
                },
            },
        );
    };

    const visibleRating = hoveredRating || rating;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Write a Review" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div>
                    <Link
                        href="/purchases"
                        className="mb-2 inline-flex items-center text-sm text-muted-foreground hover:text-foreground"
                    >
                        <ArrowLeft className="mr-1 h-4 w-4" />
                        Back to purchases
                    </Link>
                    <h1 className="text-2xl font-bold tracking-tight">
                        Write a Review
                    </h1>
                    <p className="text-muted-foreground">
                        Sharing your experience with{' '}
                        <Link
                            href={`/prompts/${purchase.prompt.slug}`}
                            className="text-primary hover:underline"
                        >
                            {purchase.prompt.title}
                        </Link>{' '}
                        by {purchase.prompt.seller.name}
                    </p>
                </div>

                <Card className="max-w-2xl">
                    <CardHeader>
                        <CardTitle>Your Review</CardTitle>
                        <CardDescription>
                            Reviews are visible to other buyers on the
                            marketplace
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            {/* Rating */}
                            <div className="space-y-2">
                                <Label>Overall rating *</Label>
                                <div className="flex items-center gap-1">
                                    {[1, 2, 3, 4, 5].map((star) => (
                                        <button
                                            key={star}
                                            type="button"
                                            aria-label={`Rate ${star} star${star > 1 ? 's' : ''}`}
                                            className="rounded p-0.5 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                            onMouseEnter={() =>
                                                setHoveredRating(star)
                                            }
                                            onMouseLeave={() =>
                                                setHoveredRating(0)
                                            }
                                            onClick={() => setRating(star)}
                                        >
                                            <Star
                                                className={`h-7 w-7 transition-colors ${
                                                    star <= visibleRating
                                                        ? 'fill-primary text-primary'
                                                        : 'text-muted-foreground'
                                                }`}
                                            />
                                        </button>
                                    ))}
                                    <span className="ml-2 text-sm text-muted-foreground">
                                        {rating > 0
                                            ? `${rating} of 5`
                                            : 'Click to rate'}
                                    </span>
                                </div>
                                {errors.rating && (
                                    <p className="text-sm text-destructive">
                                        {errors.rating}
                                    </p>
                                )}
                            </div>

                            {/* Title */}
                            <div className="space-y-2">
                                <Label htmlFor="title">Title</Label>
                                <Input
                                    id="title"
                                    placeholder="Sum up your experience"
                                    value={title}
                                    onChange={(e) => setTitle(e.target.value)}
                                />
                                {errors.title && (
                                    <p className="text-sm text-destructive">
                                        {errors.title}
                                    </p>
                                )}
                            </div>

                            {/* Content */}
                            <div className="space-y-2">
                                <Label htmlFor="content">Review</Label>
                                <Textarea
                                    id="content"
                                    rows={6}
                                    placeholder="What did you like or dislike? How well did the prompt work for you?"
                                    value={content}
                                    onChange={(e) => setContent(e.target.value)}
                                />
                                {errors.content && (
                                    <p className="text-sm text-destructive">
                                        {errors.content}
                                    </p>
                                )}
                            </div>

                            <div className="flex justify-end gap-4">
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => window.history.back()}
                                    disabled={isSubmitting}
                                >
                                    Cancel
                                </Button>
                                <Button
                                    type="submit"
                                    disabled={isSubmitting || rating === 0}
                                >
                                    {isSubmitting
                                        ? 'Submitting…'
                                        : 'Submit Review'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

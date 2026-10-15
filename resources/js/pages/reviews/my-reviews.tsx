import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { formatDate } from '@/lib/format';
import { type BreadcrumbItem } from '@/types';
import { type Paginated, type Review } from '@/types/prompt';
import { Head, Link, router } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, Pencil, Star, Trash2 } from 'lucide-react';

// The my-reviews route always eager-loads prompt and seller
type ReviewWithPrompt = Review & { prompt: NonNullable<Review['prompt']> };

interface Props {
    reviews: Paginated<ReviewWithPrompt>;
}

export default function MyReviews({ reviews }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'My Reviews' },
    ];

    const renderStars = (rating: number) => (
        <div className="flex items-center gap-0.5">
            {[1, 2, 3, 4, 5].map((star) => (
                <Star
                    key={star}
                    className={`h-4 w-4 ${star <= rating ? 'fill-primary text-primary' : 'text-muted-foreground'}`}
                />
            ))}
        </div>
    );

    const handleDelete = (id: number) => {
        if (window.confirm('Delete this review? This cannot be undone.')) {
            router.delete(`/reviews/${id}`);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="My Reviews" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">
                        My Reviews
                    </h1>
                    <p className="text-muted-foreground">
                        {reviews.total} review{reviews.total === 1 ? '' : 's'}{' '}
                        written
                    </p>
                </div>

                <div className="space-y-4">
                    {reviews.data.length > 0 ? (
                        reviews.data.map((review) => (
                            <Card key={review.id}>
                                <CardContent className="space-y-3 pt-6">
                                    <div className="flex items-start justify-between gap-4">
                                        <div>
                                            <Link
                                                href={`/prompts/${review.prompt.slug}`}
                                                className="font-medium hover:underline"
                                            >
                                                {review.prompt.title}
                                            </Link>
                                            <p className="text-sm text-muted-foreground">
                                                by {review.prompt.seller.name}
                                            </p>
                                            <div className="mt-1 flex items-center gap-2">
                                                {renderStars(review.rating)}
                                                <span className="text-xs text-muted-foreground">
                                                    {formatDate(
                                                        review.created_at,
                                                    )}
                                                </span>
                                            </div>
                                        </div>
                                        <div className="flex gap-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                asChild
                                            >
                                                <Link
                                                    href={`/reviews/${review.id}/edit`}
                                                >
                                                    <Pencil className="h-4 w-4" />
                                                </Link>
                                            </Button>
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={() =>
                                                    handleDelete(review.id)
                                                }
                                            >
                                                <Trash2 className="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                    {review.title && (
                                        <p className="font-medium">
                                            {review.title}
                                        </p>
                                    )}
                                    {review.content && (
                                        <p className="text-sm whitespace-pre-line text-muted-foreground">
                                            {review.content}
                                        </p>
                                    )}
                                    <p className="text-xs text-muted-foreground">
                                        {review.helpful_count} people found this
                                        helpful
                                    </p>
                                </CardContent>
                            </Card>
                        ))
                    ) : (
                        <Card>
                            <CardContent className="py-8 text-center">
                                <Star className="mx-auto mb-4 h-12 w-12 text-muted-foreground opacity-50" />
                                <p className="mb-4 text-muted-foreground">
                                    You have not written any reviews yet.
                                </p>
                                <Button asChild>
                                    <Link href="/purchases">
                                        Browse your purchases
                                    </Link>
                                </Button>
                            </CardContent>
                        </Card>
                    )}
                </div>

                {reviews.last_page > 1 && (
                    <div className="flex items-center justify-center gap-2">
                        <Button
                            variant="outline"
                            size="icon"
                            disabled={reviews.current_page <= 1}
                            onClick={() =>
                                router.get(
                                    '/my-reviews',
                                    { page: reviews.current_page - 1 },
                                    { preserveScroll: true },
                                )
                            }
                        >
                            <ChevronLeft className="h-4 w-4" />
                        </Button>
                        <span className="text-sm text-muted-foreground">
                            Page {reviews.current_page} of {reviews.last_page}
                        </span>
                        <Button
                            variant="outline"
                            size="icon"
                            disabled={reviews.current_page >= reviews.last_page}
                            onClick={() =>
                                router.get(
                                    '/my-reviews',
                                    { page: reviews.current_page + 1 },
                                    { preserveScroll: true },
                                )
                            }
                        >
                            <ChevronRight className="h-4 w-4" />
                        </Button>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Star, ThumbsUp } from 'lucide-react';

interface Prompt {
    id: number;
    title: string;
    slug: string;
}

interface Review {
    id: number;
    rating: number;
    title: string | null;
    content: string | null;
    helpful_count: number;
    seller_response: string | null;
    created_at: string;
    user: { id: number; name: string };
}

interface Props {
    prompt: Prompt;
    reviews: {
        data: Review[];
        current_page: number;
        last_page: number;
        total: number;
    };
    stats: {
        average: number;
        distribution: Record<string, number>;
    };
}

function formatDate(date: string) {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

export default function PromptReviews({ prompt, reviews, stats }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Marketplace', href: '/marketplace' },
        { title: prompt.title, href: `/prompts/${prompt.slug}` },
        { title: 'Reviews' },
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

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Reviews — ${prompt.title}`} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div>
                    <Link
                        href={`/prompts/${prompt.slug}`}
                        className="mb-2 inline-flex items-center text-sm text-muted-foreground hover:text-foreground"
                    >
                        <ArrowLeft className="mr-1 h-4 w-4" />
                        Back to prompt
                    </Link>
                    <h1 className="text-2xl font-bold tracking-tight">
                        Reviews
                    </h1>
                </div>

                <div className="grid gap-6 lg:grid-cols-3">
                    {/* Rating summary */}
                    <Card className="h-fit">
                        <CardHeader>
                            <CardTitle className="text-base">
                                Rating Overview
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex items-center gap-3">
                                <span className="text-4xl font-bold">
                                    {Number(stats.average).toFixed(1)}
                                </span>
                                <div className="space-y-1">
                                    {renderStars(
                                        Math.round(Number(stats.average)),
                                    )}
                                    <p className="text-xs text-muted-foreground">
                                        {reviews.total ?? reviews.data.length}{' '}
                                        reviews
                                    </p>
                                </div>
                            </div>
                            <div className="space-y-2">
                                {[5, 4, 3, 2, 1].map((stars) => {
                                    const count = Number(
                                        stats.distribution?.[stars] ?? 0,
                                    );
                                    const total = Object.values(
                                        stats.distribution ?? {},
                                    ).reduce(
                                        (sum: number, value) =>
                                            sum + Number(value),
                                        0,
                                    );
                                    const share =
                                        total > 0 ? (count / total) * 100 : 0;

                                    return (
                                        <div
                                            key={stars}
                                            className="flex items-center gap-2 text-sm"
                                        >
                                            <span className="w-3 text-muted-foreground">
                                                {stars}
                                            </span>
                                            <Star className="h-3 w-3 fill-primary text-primary" />
                                            <div className="h-2 flex-1 overflow-hidden rounded-full bg-muted">
                                                <div
                                                    className="h-full rounded-full bg-primary"
                                                    style={{
                                                        width: `${share}%`,
                                                    }}
                                                />
                                            </div>
                                            <span className="w-6 text-right text-muted-foreground">
                                                {count}
                                            </span>
                                        </div>
                                    );
                                })}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Review list */}
                    <div className="space-y-4 lg:col-span-2">
                        {reviews.data.length > 0 ? (
                            reviews.data.map((review) => (
                                <Card key={review.id}>
                                    <CardContent className="space-y-3 pt-6">
                                        <div className="flex items-start justify-between gap-4">
                                            <div>
                                                <p className="font-medium">
                                                    {review.user.name}
                                                </p>
                                                <div className="flex items-center gap-2">
                                                    {renderStars(review.rating)}
                                                    <span className="text-xs text-muted-foreground">
                                                        {formatDate(
                                                            review.created_at,
                                                        )}
                                                    </span>
                                                </div>
                                            </div>
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={() =>
                                                    router.post(
                                                        `/reviews/${review.id}/helpful`,
                                                    )
                                                }
                                            >
                                                <ThumbsUp className="mr-1 h-4 w-4" />
                                                {review.helpful_count}
                                            </Button>
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
                                        {review.seller_response && (
                                            <div className="space-y-1 rounded-lg border bg-muted p-3">
                                                <p className="text-xs font-medium">
                                                    Seller response
                                                </p>
                                                <p className="text-sm whitespace-pre-line text-muted-foreground">
                                                    {review.seller_response}
                                                </p>
                                            </div>
                                        )}
                                    </CardContent>
                                </Card>
                            ))
                        ) : (
                            <Card>
                                <CardContent className="py-8 text-center text-muted-foreground">
                                    No reviews yet. Be the first to review!
                                </CardContent>
                            </Card>
                        )}

                        {reviews.current_page < reviews.last_page && (
                            <div className="flex justify-center">
                                <Button
                                    variant="outline"
                                    onClick={() =>
                                        router.get(
                                            `/prompts/${prompt.id}/reviews`,
                                            { page: reviews.current_page + 1 },
                                            { preserveScroll: true },
                                        )
                                    }
                                >
                                    Load More
                                </Button>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

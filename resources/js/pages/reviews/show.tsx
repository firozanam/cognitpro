import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { formatDate } from '@/lib/format';
import { type BreadcrumbItem } from '@/types';
import { type Review } from '@/types/prompt';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Star, ThumbsUp } from 'lucide-react';
import { FormEvent, useState } from 'react';

// The show route always eager-loads the prompt
type ReviewWithPrompt = Review & { prompt: NonNullable<Review['prompt']> };

interface Props {
    review: ReviewWithPrompt;
}

export default function ReviewShow({ review }: Props) {
    const [response, setResponse] = useState('');
    const [errors, setErrors] = useState<Record<string, string>>({});

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Marketplace', href: '/marketplace' },
        { title: review.prompt.title, href: `/prompts/${review.prompt.slug}` },
        { title: 'Review' },
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

    const handleRespond = (e: FormEvent) => {
        e.preventDefault();
        setErrors({});

        router.post(
            `/reviews/${review.id}/respond`,
            { response },
            {
                onSuccess: () => setResponse(''),
                onError: (errs) => setErrors(errs as Record<string, string>),
            },
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Review" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div>
                    <Link
                        href={`/prompts/${review.prompt.slug}/reviews`}
                        className="mb-2 inline-flex items-center text-sm text-muted-foreground hover:text-foreground"
                    >
                        <ArrowLeft className="mr-1 h-4 w-4" />
                        All reviews
                    </Link>
                    <h1 className="text-2xl font-bold tracking-tight">
                        {review.title || `Review of ${review.prompt.title}`}
                    </h1>
                </div>

                <Card className="max-w-3xl">
                    <CardContent className="space-y-4 pt-6">
                        <div className="flex items-start justify-between gap-4">
                            <div>
                                <p className="font-medium">
                                    {review.user.name}
                                </p>
                                <div className="flex items-center gap-2">
                                    {renderStars(review.rating)}
                                    <span className="text-xs text-muted-foreground">
                                        {formatDate(review.created_at)}
                                    </span>
                                </div>
                            </div>
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() =>
                                    router.post(`/reviews/${review.id}/helpful`)
                                }
                            >
                                <ThumbsUp className="mr-1 h-4 w-4" />
                                {review.helpful_count}
                            </Button>
                        </div>

                        {review.content && (
                            <p className="text-sm whitespace-pre-line text-muted-foreground">
                                {review.content}
                            </p>
                        )}

                        {review.seller_response ? (
                            <div className="space-y-1 rounded-lg border bg-muted p-3">
                                <div className="flex items-center gap-2">
                                    <Badge variant="secondary">Seller</Badge>
                                    <span className="text-xs text-muted-foreground">
                                        {review.prompt.seller.name}
                                    </span>
                                </div>
                                <p className="text-sm whitespace-pre-line text-muted-foreground">
                                    {review.seller_response}
                                </p>
                            </div>
                        ) : (
                            <form
                                onSubmit={handleRespond}
                                className="space-y-2 rounded-lg border p-3"
                            >
                                <p className="text-sm font-medium">
                                    Respond as the seller
                                </p>
                                <Textarea
                                    rows={3}
                                    placeholder="Reply to this review publicly"
                                    value={response}
                                    onChange={(e) =>
                                        setResponse(e.target.value)
                                    }
                                />
                                {errors.response && (
                                    <p className="text-sm text-destructive">
                                        {errors.response}
                                    </p>
                                )}
                                <Button type="submit" size="sm">
                                    Post Response
                                </Button>
                            </form>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

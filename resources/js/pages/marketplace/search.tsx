import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { formatPromptPrice } from '@/lib/format';
import { type BreadcrumbItem } from '@/types';
import { type Paginated, type Prompt } from '@/types/prompt';
import { Head, Link, router } from '@inertiajs/react';
import {
    ChevronLeft,
    ChevronRight,
    Eye,
    Search,
    ShoppingBag,
    Star,
} from 'lucide-react';
import { FormEvent, useState } from 'react';

interface Props {
    prompts: Paginated<Prompt>;
    searchTerm: string;
}

export default function MarketplaceSearch({ prompts, searchTerm }: Props) {
    const [term, setTerm] = useState(searchTerm);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Marketplace', href: '/marketplace' },
        { title: 'Search' },
    ];

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        router.get('/marketplace/search', { q: term });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Search Prompts" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">
                        Search Prompts
                    </h1>
                    <p className="text-muted-foreground">
                        {prompts.total} result{prompts.total === 1 ? '' : 's'}
                        {searchTerm ? ` for "${searchTerm}"` : ''}
                    </p>
                </div>

                <form onSubmit={handleSubmit} className="flex max-w-xl gap-2">
                    <div className="relative flex-1">
                        <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={term}
                            onChange={(e) => setTerm(e.target.value)}
                            placeholder="Search prompts by title or description…"
                            className="pl-9"
                        />
                    </div>
                    <Button type="submit">Search</Button>
                </form>

                <div className="space-y-4">
                    {prompts.data.length > 0 ? (
                        prompts.data.map((prompt) => (
                            <Card
                                key={prompt.id}
                                className="transition-colors hover:border-primary/50"
                            >
                                <CardContent className="pt-6">
                                    <Link
                                        href={`/prompts/${prompt.slug}`}
                                        className="block space-y-2"
                                    >
                                        <div className="flex items-start justify-between gap-4">
                                            <div className="min-w-0">
                                                <div className="flex flex-wrap items-center gap-2">
                                                    <h3 className="line-clamp-1 font-semibold hover:underline">
                                                        {prompt.title}
                                                    </h3>
                                                    {prompt.featured && (
                                                        <Badge variant="default">
                                                            Featured
                                                        </Badge>
                                                    )}
                                                </div>
                                                <p className="line-clamp-2 text-sm text-muted-foreground">
                                                    {prompt.description}
                                                </p>
                                            </div>
                                            <div className="text-lg font-bold whitespace-nowrap">
                                                {formatPromptPrice(prompt)}
                                            </div>
                                        </div>
                                        <div className="flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
                                            <Badge variant="secondary">
                                                {prompt.category.name}
                                            </Badge>
                                            <Badge variant="outline">
                                                {prompt.ai_model}
                                            </Badge>
                                            <span className="flex items-center gap-1">
                                                <Star className="h-3.5 w-3.5 fill-primary text-primary" />
                                                {Number(prompt.rating).toFixed(
                                                    1,
                                                )}{' '}
                                                ({prompt.rating_count})
                                            </span>
                                            <span className="flex items-center gap-1">
                                                <Eye className="h-3.5 w-3.5" />
                                                {prompt.views_count}
                                            </span>
                                            <span className="flex items-center gap-1">
                                                <ShoppingBag className="h-3.5 w-3.5" />
                                                {prompt.purchases_count}
                                            </span>
                                        </div>
                                    </Link>
                                </CardContent>
                            </Card>
                        ))
                    ) : (
                        <Card>
                            <CardContent className="py-12 text-center">
                                <Search className="mx-auto mb-4 h-12 w-12 text-muted-foreground opacity-50" />
                                <p className="mb-4 text-muted-foreground">
                                    No prompts found
                                    {searchTerm ? ` for "${searchTerm}"` : ''}.
                                </p>
                                <Button asChild>
                                    <Link href="/marketplace">
                                        Browse the marketplace
                                    </Link>
                                </Button>
                            </CardContent>
                        </Card>
                    )}
                </div>

                {prompts.last_page > 1 && (
                    <div className="flex items-center justify-center gap-2">
                        <Button
                            variant="outline"
                            size="icon"
                            disabled={prompts.current_page <= 1}
                            onClick={() =>
                                router.get(
                                    '/marketplace/search',
                                    {
                                        q: searchTerm,
                                        page: prompts.current_page - 1,
                                    },
                                    { preserveScroll: true },
                                )
                            }
                        >
                            <ChevronLeft className="h-4 w-4" />
                        </Button>
                        <span className="text-sm text-muted-foreground">
                            Page {prompts.current_page} of {prompts.last_page}
                        </span>
                        <Button
                            variant="outline"
                            size="icon"
                            disabled={prompts.current_page >= prompts.last_page}
                            onClick={() =>
                                router.get(
                                    '/marketplace/search',
                                    {
                                        q: searchTerm,
                                        page: prompts.current_page + 1,
                                    },
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

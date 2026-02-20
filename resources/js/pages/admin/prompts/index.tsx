import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
    Table, 
    TableBody, 
    TableCell, 
    TableHead, 
    TableHeader, 
    TableRow 
} from '@/components/ui/table';
import { 
    DropdownMenu, 
    DropdownMenuContent, 
    DropdownMenuItem, 
    DropdownMenuLabel, 
    DropdownMenuSeparator, 
    DropdownMenuTrigger 
} from '@/components/ui/dropdown-menu';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle
} from '@/components/ui/alert-dialog';
import { Textarea } from '@/components/ui/textarea';
import {
    MoreHorizontal,
    Eye,
    Check,
    X,
    Star,
    FileText,
    Clock,
    User,
    Search,
    Filter
} from 'lucide-react';
import { type BreadcrumbItem } from '@/types';
import { useState } from 'react';

interface Prompt {
    id: number;
    title: string;
    slug: string;
    description: string;
    status: 'pending' | 'approved' | 'rejected';
    pricing_model: 'fixed' | 'pay_what_you_want' | 'free';
    price: number;
    seller: {
        id: number;
        name: string;
        email: string;
    };
    category: {
        id: number;
        name: string;
    };
    created_at: string;
    rejection_reason?: string;
}

interface Props {
    prompts: {
        data: Prompt[];
        current_page: number;
        last_page: number;
        total: number;
        per_page: number;
    };
    filters?: {
        status?: string;
        search?: string;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '/admin',
    },
    {
        title: 'Prompt Moderation',
        href: '/admin/prompts',
    },
];

export default function AdminPromptsIndex({ prompts, filters }: Props) {
    const [searchTerm, setSearchTerm] = useState(filters?.search || '');
    const [statusFilter, setStatusFilter] = useState(filters?.status || '');
    const [rejectDialogOpen, setRejectDialogOpen] = useState(false);
    const [selectedPrompt, setSelectedPrompt] = useState<Prompt | null>(null);
    const [rejectionReason, setRejectionReason] = useState('');

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'approved':
                return <Badge variant="default" className="bg-green-500">Approved</Badge>;
            case 'rejected':
                return <Badge variant="destructive">Rejected</Badge>;
            case 'pending':
            default:
                return <Badge variant="secondary">Pending</Badge>;
        }
    };

    const getPricingBadge = (model: string, price: number | string) => {
        const numPrice = typeof price === 'string' ? parseFloat(price) : price;
        switch (model) {
            case 'free':
                return <Badge variant="outline">Free</Badge>;
            case 'pay_what_you_want':
                return <Badge variant="outline">Pay What You Want</Badge>;
            default:
                return <Badge variant="outline">${(numPrice || 0).toFixed(2)}</Badge>;
        }
    };

    const handleApprove = (promptId: number) => {
        router.post(`/admin/prompts/${promptId}/approve`, {}, {
            preserveScroll: true,
        });
    };

    const handleReject = () => {
        if (!selectedPrompt || !rejectionReason.trim()) return;

        router.post(`/admin/prompts/${selectedPrompt.id}/reject`, {
            reason: rejectionReason,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                setRejectDialogOpen(false);
                setSelectedPrompt(null);
                setRejectionReason('');
            },
        });
    };

    const handleFeature = (promptId: number) => {
        router.post(`/admin/prompts/${promptId}/featured`, {}, {
            preserveScroll: true,
        });
    };

    const handleSearch = () => {
        router.get('/admin/prompts', {
            search: searchTerm,
            status: statusFilter,
        }, {
            preserveState: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Prompt Moderation" />
            
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Prompt Moderation</h1>
                        <p className="text-muted-foreground">
                            Review and manage prompt submissions
                        </p>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Prompts</CardTitle>
                            <FileText className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{prompts.total}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Pending Review</CardTitle>
                            <Clock className="h-4 w-4 text-orange-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {prompts.data.filter(p => p.status === 'pending').length}
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Approved</CardTitle>
                            <Check className="h-4 w-4 text-green-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {prompts.data.filter(p => p.status === 'approved').length}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filters */}
                <Card>
                    <CardContent className="pt-6">
                        <div className="flex flex-col gap-4 sm:flex-row">
                            <div className="flex-1">
                                <div className="relative">
                                    <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                                    <input
                                        type="text"
                                        placeholder="Search prompts..."
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 pl-8 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                    />
                                </div>
                            </div>
                            <select
                                value={statusFilter}
                                onChange={(e) => setStatusFilter(e.target.value)}
                                className="flex h-10 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                            >
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            <Button onClick={handleSearch}>
                                <Filter className="mr-2 h-4 w-4" />
                                Apply Filters
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                {/* Prompts Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>Prompts</CardTitle>
                        <CardDescription>
                            A list of all prompts submitted to the marketplace
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Title</TableHead>
                                    <TableHead>Seller</TableHead>
                                    <TableHead>Category</TableHead>
                                    <TableHead>Price</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Submitted</TableHead>
                                    <TableHead className="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {prompts.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell colSpan={7} className="text-center py-8">
                                            <div className="flex flex-col items-center gap-2">
                                                <FileText className="h-8 w-8 text-muted-foreground" />
                                                <p className="text-muted-foreground">No prompts found</p>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    prompts.data.map((prompt) => (
                                        <TableRow key={prompt.id}>
                                            <TableCell>
                                                <div>
                                                    <div className="font-medium">{prompt.title}</div>
                                                    <div className="text-sm text-muted-foreground line-clamp-1">
                                                        {prompt.description}
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-2">
                                                    <User className="h-4 w-4 text-muted-foreground" />
                                                    <div>
                                                        <div className="font-medium">{prompt.seller.name}</div>
                                                        <div className="text-sm text-muted-foreground">
                                                            {prompt.seller.email}
                                                        </div>
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>{prompt.category.name}</TableCell>
                                            <TableCell>
                                                {getPricingBadge(prompt.pricing_model, prompt.price)}
                                            </TableCell>
                                            <TableCell>{getStatusBadge(prompt.status)}</TableCell>
                                            <TableCell>
                                                {new Date(prompt.created_at).toLocaleDateString()}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger asChild>
                                                        <Button variant="ghost" size="icon">
                                                            <MoreHorizontal className="h-4 w-4" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end">
                                                        <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                                        <DropdownMenuItem asChild>
                                                            <Link href={`/prompts/${prompt.slug}`}>
                                                                <Eye className="mr-2 h-4 w-4" />
                                                                View Prompt
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuSeparator />
                                                        {prompt.status === 'pending' && (
                                                            <>
                                                                <DropdownMenuItem 
                                                                    onClick={() => handleApprove(prompt.id)}
                                                                    className="text-green-600"
                                                                >
                                                                    <Check className="mr-2 h-4 w-4" />
                                                                    Approve
                                                                </DropdownMenuItem>
                                                                <DropdownMenuItem 
                                                                    onClick={() => {
                                                                        setSelectedPrompt(prompt);
                                                                        setRejectDialogOpen(true);
                                                                    }}
                                                                    className="text-red-600"
                                                                >
                                                                    <X className="mr-2 h-4 w-4" />
                                                                    Reject
                                                                </DropdownMenuItem>
                                                            </>
                                                        )}
                                                        {prompt.status === 'approved' && (
                                                            <DropdownMenuItem onClick={() => handleFeature(prompt.id)}>
                                                                <Star className="mr-2 h-4 w-4" />
                                                                Toggle Featured
                                                            </DropdownMenuItem>
                                                        )}
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>

                        {/* Pagination */}
                        {prompts.last_page > 1 && (
                            <div className="flex items-center justify-center gap-2 mt-4">
                                <Button
                                    variant="outline"
                                    disabled={prompts.current_page === 1}
                                    onClick={() => router.get('/admin/prompts', { page: prompts.current_page - 1 })}
                                >
                                    Previous
                                </Button>
                                <span className="text-sm text-muted-foreground">
                                    Page {prompts.current_page} of {prompts.last_page}
                                </span>
                                <Button
                                    variant="outline"
                                    disabled={prompts.current_page === prompts.last_page}
                                    onClick={() => router.get('/admin/prompts', { page: prompts.current_page + 1 })}
                                >
                                    Next
                                </Button>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>

            {/* Reject Dialog */}
            <AlertDialog open={rejectDialogOpen} onOpenChange={setRejectDialogOpen}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Reject Prompt</AlertDialogTitle>
                        <AlertDialogDescription>
                            Please provide a reason for rejecting "{selectedPrompt?.title}". This will be shared with the seller.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <div className="py-4">
                        <Textarea
                            placeholder="Enter rejection reason..."
                            value={rejectionReason}
                            onChange={(e) => setRejectionReason(e.target.value)}
                            rows={4}
                        />
                    </div>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction 
                            onClick={handleReject}
                            disabled={!rejectionReason.trim()}
                            className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                        >
                            Reject Prompt
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </AppLayout>
    );
}

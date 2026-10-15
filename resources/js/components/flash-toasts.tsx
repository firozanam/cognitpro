import { usePage } from '@inertiajs/react';
import { CheckCircle2, CircleAlert, Info, X } from 'lucide-react';
import { useEffect, useState } from 'react';

interface FlashMessage {
    kind: 'success' | 'error' | 'info';
    text: string;
}

const KIND_STYLES: Record<FlashMessage['kind'], string> = {
    success: 'border-border bg-background text-foreground',
    error: 'border-destructive/50 bg-background text-foreground',
    info: 'border-border bg-background text-foreground',
};

const KIND_ICONS: Record<FlashMessage['kind'], React.ReactNode> = {
    success: <CheckCircle2 className="h-4 w-4 text-primary" />,
    error: <CircleAlert className="h-4 w-4 text-destructive" />,
    info: <Info className="h-4 w-4 text-muted-foreground" />,
};

function Toast({
    message,
    onDismiss,
}: {
    message: FlashMessage & { id: number };
    onDismiss: (id: number) => void;
}) {
    useEffect(() => {
        const timer = setTimeout(() => onDismiss(message.id), 5000);

        return () => clearTimeout(timer);
    }, [message.id, onDismiss]);

    return (
        <div
            role="status"
            className={`pointer-events-auto flex w-full items-center gap-3 rounded-lg border px-4 py-3 shadow-lg ${KIND_STYLES[message.kind]}`}
        >
            {KIND_ICONS[message.kind]}
            <p className="flex-1 text-sm">{message.text}</p>
            <button
                type="button"
                aria-label="Dismiss notification"
                onClick={() => onDismiss(message.id)}
                className="rounded p-1 text-muted-foreground hover:text-foreground"
            >
                <X className="h-4 w-4" />
            </button>
        </div>
    );
}

/**
 * Renders session flash messages (shared as `flash` by
 * HandleInertiaRequests) as auto-dismissing toasts.
 */
export function FlashToasts() {
    const { flash } = usePage().props as {
        flash?: { success?: string; error?: string; info?: string };
    };
    const [toasts, setToasts] = useState<(FlashMessage & { id: number })[]>([]);

    useEffect(() => {
        const incoming: FlashMessage[] = [];
        if (flash?.success)
            incoming.push({ kind: 'success', text: flash.success });
        if (flash?.error) incoming.push({ kind: 'error', text: flash.error });
        if (flash?.info) incoming.push({ kind: 'info', text: flash.info });

        if (incoming.length > 0) {
            setToasts((prev) => {
                // StrictMode runs effects twice in development — skip duplicates
                const fresh = incoming.filter(
                    (message) =>
                        !prev.some(
                            (toast) =>
                                toast.kind === message.kind &&
                                toast.text === message.text,
                        ),
                );

                return fresh.length > 0
                    ? [
                          ...fresh.map((message, index) => ({
                              ...message,
                              id: Date.now() + index,
                          })),
                          ...prev,
                      ].slice(0, 3)
                    : prev;
            });
        }
    }, [flash?.success, flash?.error, flash?.info]);

    const dismiss = (id: number) =>
        setToasts((prev) => prev.filter((toast) => toast.id !== id));

    if (toasts.length === 0) {
        return null;
    }

    return (
        <div className="pointer-events-none fixed inset-x-0 bottom-0 z-[100] flex flex-col items-center gap-2 p-4 sm:items-end">
            {toasts.map((toast) => (
                <Toast key={toast.id} message={toast} onDismiss={dismiss} />
            ))}
        </div>
    );
}

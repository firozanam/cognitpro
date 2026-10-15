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

function Toast({ message }: { message: FlashMessage }) {
    const [visible, setVisible] = useState(true);

    useEffect(() => {
        const timer = setTimeout(() => setVisible(false), 5000);

        return () => clearTimeout(timer);
    }, []);

    if (!visible) {
        return null;
    }

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
                onClick={() => setVisible(false)}
                className="rounded p-1 text-muted-foreground hover:text-foreground"
            >
                <X className="h-4 w-4" />
            </button>
        </div>
    );
}

/**
 * Renders session flash messages (shared as `flash` by
 * HandleInertiaRequests) as self-dismissing toasts.
 */
export function FlashToasts() {
    const { flash } = usePage().props as {
        flash?: { success?: string; error?: string; info?: string };
    };

    const messages: FlashMessage[] = [];
    if (flash?.success) messages.push({ kind: 'success', text: flash.success });
    if (flash?.error) messages.push({ kind: 'error', text: flash.error });
    if (flash?.info) messages.push({ kind: 'info', text: flash.info });

    if (messages.length === 0) {
        return null;
    }

    return (
        <div className="pointer-events-none fixed inset-x-0 bottom-0 z-[100] flex flex-col items-center gap-2 p-4 sm:items-end">
            {messages.map((message) => (
                <Toast
                    key={`${message.kind}:${message.text}`}
                    message={message}
                />
            ))}
        </div>
    );
}

import { Pencil } from 'lucide-react';
import type * as React from 'react';
import { Button } from '@/components/ui/button';

/**
 * Pulsante matita: usato come DialogTrigger nei componenti ped-admin/*.
 * Il chiamante decide QUANDO mostrarlo (solo se actions.canEdit, cioè solo admin).
 */
export function EditButton({
    label = 'Modifica',
    ...props
}: React.ComponentProps<typeof Button> & { label?: string }) {
    return (
        <Button
            type="button"
            variant="ghost"
            size="icon-sm"
            aria-label={label}
            className="shrink-0 text-muted-foreground hover:text-foreground"
            {...props}
        >
            <Pencil className="size-3.5" />
        </Button>
    );
}

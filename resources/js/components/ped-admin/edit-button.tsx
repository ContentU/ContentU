import { Pencil } from 'lucide-react';
import { Button } from '@/components/ui/button';

/**
 * Pulsante matita: usato come DialogTrigger nei componenti ped-admin/*.
 * Il chiamante decide QUANDO mostrarlo (solo se actions.canEdit, cioè solo admin).
 */
export function EditButton({ label = 'Modifica' }: { label?: string }) {
    return (
        <Button
            type="button"
            variant="ghost"
            size="icon-sm"
            aria-label={label}
            className="shrink-0 text-muted-foreground hover:text-foreground"
        >
            <Pencil className="size-3.5" />
        </Button>
    );
}

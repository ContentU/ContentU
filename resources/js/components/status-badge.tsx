import { Badge } from '@/components/ui/badge';

export type DomainStatus =
    | 'draft'
    | 'in_review'
    | 'approved'
    | 'scheduled'
    | 'published'
    | 'needs_changes'
    | 'closed';

const LABELS: Record<DomainStatus, string> = {
    draft: 'Bozza',
    in_review: 'In revisione',
    approved: 'Approvato',
    scheduled: 'Programmato',
    published: 'Pubblicato',
    needs_changes: 'Richiesta modifica',
    closed: 'Concluso',
};

// Solo utility derivate dai token di app.css — vedi regola DS4.
const STYLES: Record<DomainStatus, string> = {
    draft: 'bg-status-draft-bg text-status-draft',
    in_review: 'bg-status-review-bg text-status-review',
    approved: 'bg-status-approved-bg text-status-approved',
    scheduled: 'bg-status-scheduled-bg text-status-scheduled',
    published: 'bg-status-published-bg text-status-published',
    needs_changes: 'bg-status-review-bg text-status-review',
    closed: 'bg-status-published-bg text-status-published',
};

export function StatusBadge({
    status,
    label,
}: {
    status: DomainStatus;
    label?: string;
}) {
    return (
        <Badge variant="outline" className={STYLES[status]}>
            {label ?? LABELS[status]}
        </Badge>
    );
}

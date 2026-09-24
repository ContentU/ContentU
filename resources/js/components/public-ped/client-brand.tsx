export type PublicClient = {
    name: string;
    initials: string;
    logoUrl: string | null;
    topicsArtifactUrl?: string | null;
    shootingArtifactUrl?: string | null;
};

const SIZE_CLASSES = {
    md: 'size-9 text-sm',
    lg: 'size-16 text-xl',
} as const;

export function ClientBrandMark({
    client,
    size = 'md',
}: {
    client: PublicClient;
    size?: keyof typeof SIZE_CLASSES;
}) {
    return client.logoUrl ? (
        <img
            src={client.logoUrl}
            alt={client.name}
            className={`${SIZE_CLASSES[size]} rounded-md object-contain`}
        />
    ) : (
        <div
            className={`flex ${SIZE_CLASSES[size]} items-center justify-center rounded-md bg-muted font-serif`}
        >
            {client.initials}
        </div>
    );
}

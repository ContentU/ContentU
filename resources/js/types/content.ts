export type ContentStatus =
    | 'draft'
    | 'in_review'
    | 'approved'
    | 'scheduled'
    | 'published'
    | 'needs_changes';

export type FeedContent = {
    id: number;
    title: string;
    caption: string | null;
    hashtags: string | null;
    resourceUrl: string | null;
    coverResourceUrl: string | null;
    publishAt: string; // ISO
    publishAtLabel: string; // "gio 17 ott", già formattato lato server
    status: ContentStatus;
    typeLabel: string; // "CAROSELLO"
    typeKey: string;
    channels: string[];
    tags: { id: number; label: string }[];
    isToday: boolean;
    comments: ContentComment[];
};

/** Cosa può fare chi sta guardando. Deciso dal server, mai dedotto nel front-end. */
export type FeedViewerActions = {
    canApprove: boolean;
    canReject: boolean;
    canComment: boolean;
    canEdit: boolean; // SEMPRE false per il cliente esterno
};

export type ContentComment = {
    id: number;
    authorLabel: 'Cliente' | 'Team';
    authorName: string;
    body: string;
    createdAtLabel: string;
};

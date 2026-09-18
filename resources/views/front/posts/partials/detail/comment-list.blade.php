@livewire('front.discussion.comments-list', [
    'commentable' => $post,
    'averageRating' => isset($ratingSummary) ? $ratingSummary->averageRating : null,
    'ratingCount' => isset($ratingSummary) ? $ratingSummary->ratingCount : 0,
])

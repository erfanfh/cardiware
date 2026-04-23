@extends('front.master')

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-8">
        @include('front.posts.partials.detail.title')

        <section class="grid grid-cols-1 lg:grid-cols-10 gap-6">
            <div class="lg:col-span-7 space-y-6">
                @include('front.posts.partials.detail.content')
                @include('front.posts.partials.detail.image-gallery')
                {{--                @include('front.posts.partials.detail.video')--}}
                @include('front.posts.partials.detail.related-posts')
                @livewire('front.discussion.send-comment-form', [
                    'commentable'=> $post,
                    'key'=> 'send-comment-form-'.$post->getMorphClass().'-'.$post->getKey()
                ])
                @livewire('front.discussion.comments-list', ['commentable' => $post])
            </div>

            <div class="lg:col-span-3">
                @include('front.sidebar.index')
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    @vite(['resources/js/front/post-detail/index.js'])
    <script>
        function commentsBlock() {
            return {
                sort: 'new',
                newRating: 5,
                replyTo: null,
                comments: [
                    {
                        id: 1, name: 'Amir', time: '۲ ساعت پیش', rating: 5, likes: 12, liked: false,
                        text: 'روی نسخه استیم هم جواب میده؟ چون مسیر نصب من فرق داره و ارور می‌گیرم…',
                        replies: [{
                            id: '1r1',
                            name: 'ModdingWay',
                            time: '۱ ساعت پیش',
                            text: 'بله، فقط مسیر نصب را دقیق انتخاب کنید. اگر ارور داشتید، لاگ را ارسال کنید.'
                        }]
                    },
                    {
                        id: 2,
                        name: 'Nima',
                        time: 'دیروز',
                        rating: 4,
                        likes: 7,
                        liked: false,
                        text: 'بعد از آپدیت FPS بهتر شده، ولی وقتی Alt+Tab می‌کنم یه لگ کوتاه میاد…',
                        replies: []
                    },
                    {
                        id: 3,
                        name: 'Sara',
                        time: '۳ روز پیش',
                        rating: 5,
                        likes: 19,
                        liked: false,
                        text: 'پیشنهاد می‌کنم گزینه بکاپ خودکار قبل از نصب اضافه کنید خیلی کاربردیه…',
                        replies: []
                    },
                ],
                sortedComments() {
                    const arr = [...this.comments];
                    if (this.sort === 'top') return arr.sort((a, b) => b.likes - a.likes);
                    if (this.sort === 'high') return arr.sort((a, b) => b.rating - a.rating);
                    return arr;
                },
                toggleLike(c) {
                    c.liked = !c.liked;
                    c.likes += c.liked ? 1 : -1;
                },
                startReply(c) {
                    this.replyTo = (this.replyTo === c.id) ? null : c.id;
                },
                copyText(text) {
                    navigator.clipboard?.writeText(text);
                }
            }
        }
    </script>
@endpush

<?php

namespace App\Http\Controllers;

use Validator;
use Auth;
use DB;
use App\Http\Requests;
use App\Article;
use Illuminate\Http\Request;


class ArticleController extends Controller
{
    const ITEMS_PER_PAGE = 20;

    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * 記事投稿用フォームを表示させる
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|void
     */
    public function newForm()
    {
        return view('article.form', [
                'article' => new Article(['status' => 'draft']),
            ]
        );
    }

    /**
     * 記事編集用フォームを表示させる。
     * 指定された記事IDが存在しない場合、編集権がない場合はHTTPステータス404を返す。
     *
     * @param $articleId 記事ID
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|void
     */
    public function editForm($articleId)
    {
        $article = Article::find($articleId);
        // If article is not found, abort request.
        if ( is_null($article) ) {
            return abort(404);
        }
        // 閲覧権がない場合
        // MEMO: モデル側に書く？(Auth::userが必要なので、こっちでもよい？)
        if (Auth::user()->id != $article->author_id) {
            return abort(404);
        }
        return view('article.form', [
            'article' => $article,
        ]);

    }

    /**
     * 記事を登録(新規,変更両方)する
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function postOne(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'articleTitle' => 'required',
            'articleBody' => 'required',
            'articleStatus' => 'required',
            ]
        );
        // If validate is NG, return form view
        if ( $validator->fails() ) {
            return view('article.form', ['errors' => $validator->errors()]);
        }

        // Post validated values
        $params = [
            'title' => $request->input('articleTitle'),
            'body' => $request->input('articleBody'),
            'status' => $request->input('articleStatus'),
        ];
        if ($request->input('_article_id')) {
            // TODO: 編集権がない場合は、ここでエラー処理
            $article = Article::find($request->input('_article_id'));
            $message = 'Article is updated';
        } else {
            $article = Article::create(['author_id' => Auth::user()->id]);
            $message = 'New article is created';
        }
        foreach ($params as $attr => $val) {
            $article->{$attr} = $val;
        }
        DB::transaction(function () use ($article, $request, $message)
        {
            $article->save();
            $request->session()->flash('flash_message', $message);
        });
        return redirect(route('get_article_single', ['articleId' => $article->id]));
    }

    /**
     * 指定された記事を表示する。
     * 存在しない記事IDや閲覧権がない記事IDが指定されたらHTTPステータス404を返す。
     *
     * @param $articleId 記事ID
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|void
     */
    public function getOne($articleId)
    {
        $article = Article::find($articleId);
        // If article is not found, abort request.
        if ( is_null($article) ) {
            return abort(404);
        }
        // 閲覧権がない場合
        // MEMO: モデル側に書く？(Auth::userが必要なので、こっちでもよい？)
        if ($article->status != 'draft' && Auth::user()->id != $article->author_id) {
            return abort(404);
        }

        // Render article
        return view('article.single', [
            'article' => $article,
            'parser' => new \cebe\markdown\GithubMarkdown(),
        ]);
    }

    /**
     * 表示可能な記事リストを表示する。
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getList()
    {
        // TODO: モデルクラスに移植する？
        $articles = Article::latest()->where('status', 'internal')->paginate(static::ITEMS_PER_PAGE);

        // TODO: 記事がない場合の処理が必要？

        // Render articles
        return view('article.list', [
            'articles' => $articles,
        ]);
    }
}

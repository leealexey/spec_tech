<?php

namespace app\modules\admin\models;

use app\modules\admin\Module;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Html;
use yii\helpers\StringHelper;

/**
 * This is the model class for table "article".
 *
 * @property integer $id
 * @property string $slug
 * @property string $title
 * @property string $body
 * @property string $view
 * @property integer $category_id
 * @property string $thumbnail_base_url
 * @property string $thumbnail_path
 * @property integer $author_id
 * @property integer $updater_id
 * @property integer $status
 * @property integer $published_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property ArticleCategory $category
 * @property User $author
 * @property User $updater
 * @property ArticleAttachment[] $articleAttachments
 */
class Article extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'article';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['slug', 'title', 'body', 'category_id'], 'required'],
            [['body'], 'string'],
            [['category_id', 'author_id', 'updater_id', 'status', 'published_at', 'created_at', 'updated_at'], 'integer'],
            [['slug', 'thumbnail_base_url', 'thumbnail_path'], 'string', 'max' => 1024],
            ['slug', 'match', 'pattern' => '/^[0-9a-zA-Z_-]+$/', 'message' => 'Допускаются только буквы латинского алфавита, тире и нижнее подчеркивание'],
            [['title'], 'string', 'max' => 512],
            [['view'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Module::t('app', 'ID'),
            'slug' => Module::t('app', 'Slug'),
            'title' => Module::t('app', 'Title'),
            'body' => Module::t('app', 'Body'),
            'view' => Module::t('app', 'View'),
            'category_id' => Module::t('app', 'Category ID'),
            'thumbnail_base_url' => Module::t('app', 'Thumbnail Base Url'),
            'thumbnail_path' => Module::t('app', 'Thumbnail Path'),
            'author_id' => Module::t('app', 'Author ID'),
            'updater_id' => Module::t('app', 'Updater ID'),
            'status' => Module::t('app', 'Status'),
            'published_at' => Module::t('app', 'Published At'),
            'created_at' => Module::t('app', 'Created At'),
            'updated_at' => Module::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(ArticleCategory::className(), ['id' => 'category_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdater()
    {
        return $this->hasOne(User::className(), ['id' => 'updater_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArticleAttachments()
    {
        return $this->hasMany(ArticleAttachment::className(), ['article_id' => 'id']);
    }

    /**
     * @param $category
     * @param null $limit
     * @return $this
     */
    public static function getByCategory($category, $limit = null)
    {
        $categoryId = ArticleCategory::getIdBySlug($category);
        $query = self::find()->where(['category_id' => $categoryId])->orderBy(['id' => SORT_DESC]);
        if ($limit) {
            $query->limit($limit);
        }
        return $query->all();
    }

    /**
     * @return array
     */
    public static function slideNews()
    {
        $items = self::getByCategory('news', 5);
        $slides = [];
        foreach ($items as $item) {
            $title = Html::tag('div', date('d.m.Y', $item->created_at), ['class' => 'news-title']);
            $text = Html::tag('div', $item->body, ['class' => 'news-text']);
            $slides[] = Html::tag('div', $title . $text);
        }
        return $slides;
    }

    /**
     * @return array
     */
    public static function slideEquipment()
    {
        $items = self::getByCategory('equipment');
        $slides = [];
        foreach ($items as $item) {
            $avatar = Html::tag('div', Html::img($item->thumbnail_path), ['class' => 'card-avatar']);
            $header = Html::tag('div', $avatar, ['class' => 'card-header']);
            $body = Html::tag('div', $item->title, ['class' => 'card-body']);
            $button = Html::button('Подробнее', [
                'data-toggle' => 'modal',
                'data-target' => '#modalCard',
                'data-id' => $item->id,
                'data-title' => $item->title
            ]);
            $footer = Html::tag('div', $button, ['class' => 'card-footer']);

            $slides[] = Html::tag('div', $header . $body . $footer, ['class' => 'card-md card-black']);
        }
        return $slides;
    }

    /**
     * @return array
     */
    public static function slideBranches()
    {
        $items = self::getByCategory('branches');
        $slides = [];
        foreach ($items as $item) {
            $title = Html::tag('div', $item->title, ['class' => 'card-title']);
            $avatar = Html::tag('div', Html::img($item->thumbnail_path), ['class' => 'card-avatar']);
            $header = Html::tag('div', $title . $avatar, ['class' => 'card-header']);
            $body = Html::tag('div', StringHelper::truncateWords($item->body, 20), ['class' => 'card-body']);
            $button = Html::button('Подробнее', [
                'data-toggle' => 'modal',
                'data-target' => '#modalCard',
                'data-id' => $item->id,
                'data-title' => $item->title
            ]);
            $footer = Html::tag('div', $button, ['class' => 'card-footer']);

            $slides[] = Html::tag('div', $header . $body . $footer, ['class' => 'card-sm ' . $item->view]);
        }
        return $slides;
    }
}

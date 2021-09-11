<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%posts}}`.
 */
class m210911_060238_create_posts_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%post}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
            'body' => $this->text(),
            'user_id' => $this->integer()->notNull(),
            'category_id' => $this->integer()
        ]);

        $this->addForeignKey('post_user_fk', 'post', 'user_id', 'user', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('post_category_fk', 'post', 'category_id', 'category', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%post}}');
    }
}

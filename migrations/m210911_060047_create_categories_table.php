<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%categories}}`.
 */
class m210911_060047_create_categories_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%category}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'user_id' => $this->integer()->notNull(),
            'parent_id' => $this->integer()
        ]);

        $this->addForeignKey('category_user_fk', 'category', 'user_id', 'user', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('category_parent_fk', 'category', 'parent_id', 'category', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('category_uniq', 'category', ['name', 'parent_id'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%category}}');
    }
}

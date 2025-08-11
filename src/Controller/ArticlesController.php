<?php

namespace App\Controller;

class ArticlesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->Authorization->skipAuthorization();
        $articles = $this->paginate($this->Articles);
        $this->set(compact('articles'));
    }

    public function view($slug = null)
    {
        $article = $this->Articles->findBySlug($slug)->contain('Tags')->firstOrFail();
        $this->Authorization->skipAuthorization();
        $this->set(compact('article'));
    }

    public function add()
    {
        $article = $this->Articles->newEmptyEntity();
        $this->Authorization->authorize($article);
        if ($this->request->is('post')) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());

            // Hardcoding the user_id is temporary, and will be removed later
            // when we build authentication out.

            
           $article->user_id = $this->request->getAttribute('identity')->getIdentifier();

            if ($this->Articles->save($article)) {
                $this->Flash->success(__('Your article has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('Unable to add your article.'));
            }
        }
        //Get the list of tags to display in the form
        $tags = $this->Articles->Tags->find('list')->all();
        $this->set('tags', $tags);
        $this->set('article', $article);
    }

    public function edit($slug = null)
    {
        $article = $this->Articles->findBySlug($slug)->contain('Tags')->firstOrFail();
        $this->Authorization->authorize($article);
        if (!$article) {
            throw new \Cake\Datasource\Exception\RecordNotFoundException('Article not found');
        }

        if ($this->request->is(['post', 'put'])) {
            $article = $this->Articles->patchEntity($article, $this->request->getData(),[
                'accessibleFields' => ['user_id' => false]
            ]);

            if ($this->Articles->save($article)) {
                $this->Flash->success(__('Your article has been updated.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('Unable to update your article.'));
            }
        }
        // Get a list of tags.
        $tags = $this->Articles->Tags->find('list')->all();

        // Set tags to the view context
        $this->set('tags', $tags);
        $this->set('article', $article);
    }

    public function delete($slug){
        $this->request->allowMethod(['post', 'delete']);
        $article = $this->Articles->findBySlug($slug)->firstOrFail();
        if (!$article) {
            throw new \Cake\Datasource\Exception\RecordNotFoundException('Article not found');
        }
        $this->Authorization->authorize($article);

        if ($this->Articles->delete($article)) {
            $this->Flash->success(__('The article with id: {0} has been deleted.', $article->id));
        } else {
            $this->Flash->error(__('The article could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }

    public function tags(...$tags)
{
    $this->Authorization->skipAuthorization();
    // The 'pass' key is provided by CakePHP and contains all
    // the passed URL path segments in the request.
    $tags = $this->request->getParam('pass');

    // Use the ArticlesTable to find tagged articles.
    $articles = $this->Articles->find('tagged', tags: $tags)
        ->all();

    // Pass variables into the view template context.
    $this->set([
        'articles' => $articles,
        'tags' => $tags
    ]);
}
}
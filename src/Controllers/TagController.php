<?php
class TagController
{
	/**
	* @route /tags
	* @route /tags/{page}
	* @route /tags/{filter}
	* @route /tags/{filter}/{page}
	* @validate filter [a-zA-Z\32:,_-]+
	* @validate page \d*
	*/
	public function listAction($filter = null, $page = 1)
	{
		$this->context->viewName = 'tag-list-wrapper';

		PrivilegesHelper::confirmWithException(Privilege::ListTags);
		$suppliedFilter = $filter ?: InputHelper::get('filter') ?: 'order:alpha,asc';
		$page = max(1, intval($page));
		$tagsPerPage = intval($this->config->browsing->tagsPerPage);

		$tags = TagSearchService::getEntitiesRows($suppliedFilter, $tagsPerPage, $page);
		$tagCount = TagSearchService::getEntityCount($suppliedFilter);
		$pageCount = ceil($tagCount / $tagsPerPage);
		$page = min($pageCount, $page);
		$this->context->filter = $suppliedFilter;
		$this->context->transport->tags = $tags;

		if ($this->context->json)
		{
			$this->context->transport->tags = array_values(array_map(function($tag) {
				return ['name' => $tag['name'], 'count' => $tag['post_count']];
			}, $this->context->transport->tags));
		}
		else
		{
			$this->context->transport->paginator = new StdClass;
			$this->context->transport->paginator->page = $page;
			$this->context->transport->paginator->pageCount = $pageCount;
			$this->context->transport->paginator->entityCount = $tagCount;
			$this->context->transport->paginator->entities = $tags;
		}
	}

	/**
	* @route /tag/merge
	*/
	public function mergeAction()
	{
		$this->context->viewName = 'tag-list-wrapper';

		PrivilegesHelper::confirmWithException(Privilege::MergeTags);
		if (InputHelper::get('submit'))
		{
			TagModel::removeUnused();

			$suppliedSourceTag = InputHelper::get('source-tag');
			$suppliedSourceTag = TagModel::validateTag($suppliedSourceTag);

			$suppliedTargetTag = InputHelper::get('target-tag');
			$suppliedTargetTag = TagModel::validateTag($suppliedTargetTag);

			TagModel::merge($suppliedSourceTag, $suppliedTargetTag);

			\Chibi\UrlHelper::forward(\Chibi\UrlHelper::route('tag', 'list'));
			LogHelper::log('{user} merged {source} with {target}', ['source' => TextHelper::reprTag($suppliedSourceTag), 'target' => TextHelper::reprTag($suppliedTargetTag)]);
			StatusHelper::success();
		}
	}

	/**
	* @route /tag/rename
	*/
	public function renameAction()
	{
		$this->context->viewName = 'tag-list-wrapper';

		PrivilegesHelper::confirmWithException(Privilege::MergeTags);
		if (InputHelper::get('submit'))
		{
			TagModel::removeUnused();

			$suppliedSourceTag = InputHelper::get('source-tag');
			$suppliedSourceTag = TagModel::validateTag($suppliedSourceTag);

			$suppliedTargetTag = InputHelper::get('target-tag');
			$suppliedTargetTag = TagModel::validateTag($suppliedTargetTag);

			TagModel::rename($suppliedSourceTag, $suppliedTargetTag);

			\Chibi\UrlHelper::forward(\Chibi\UrlHelper::route('tag', 'list'));
			LogHelper::log('{user} renamed {source} to {target}', ['source' => TextHelper::reprTag($suppliedSourceTag), 'target' => TextHelper::reprTag($suppliedTargetTag)]);
			StatusHelper::success();
		}
	}

	/**
	* @route /mass-tag-redirect
	*/
	public function massTagRedirectAction()
	{
		$this->context->viewName = 'tag-list-wrapper';

		PrivilegesHelper::confirmWithException(Privilege::MassTag);
		if (InputHelper::get('submit'))
		{
			$suppliedQuery = InputHelper::get('query');
			if (!$suppliedQuery)
				$suppliedQuery = ' ';
			$suppliedTag = InputHelper::get('tag');
			if (!empty($suppliedTag))
				$suppliedTag = TagModel::validateTag($suppliedTag);
			\Chibi\UrlHelper::forward(\Chibi\UrlHelper::route('post', 'list', ['source' => 'mass-tag', 'query' => $suppliedQuery, 'additionalInfo' => $suppliedTag]));
		}
	}
}

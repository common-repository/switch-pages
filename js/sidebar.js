( function( plugins, editPost, element, components, data, compose ) {

	const el = element.createElement;
	
	const { Fragment } = element;
	const { registerPlugin } = plugins;
	const { PluginSidebar, PluginSidebarMoreMenuItem } = editPost;
	const { PanelBody, SelectControl } = components;
	const { withSelect, withDispatch } = data;
	
	const PostsDropdownControl = compose.compose(
		// withDispatch allows to save the selected post ID into post meta
		withDispatch( function( dispatch, props ) {
			return {
				setMetaValue: function( metaValue ) {
					dispatch( 'core/editor' ).editPost(
						{ meta: { [ props.metaKey ]: metaValue } }
					);
				}
			}
		} ),
		// withSelect allows to get posts for our SelectControl and also to get the post meta value
		withSelect( function( select, props ) {
			const postType = select( 'core/editor' ).getCurrentPostType();
			var query = { 
				per_page : -1, // set -1 to display ALL
				status : [ 'publish', 'pending', 'draft', 'future', 'private', 'inherit', 'trash' ], // or [ 'publish', 'draft', 'future' ]
			}
			
			return {
				posts: select( 'core' ).getEntityRecords( 'postType', postType, query ),
				metaValue: select( 'core/editor' ).getEditedPostAttribute( 'meta' )[ props.metaKey ],
			}
		} ) )( function( props ) {
			
			// options for SelectControl
			var options = [];
			
			// if posts found
			if( props.posts ) {
				options.push( { value: 0, label: 'Select a post/page' } );
				props.posts.forEach((post) => { // simple foreach loop
					options.push({value:post.id, label:post.title.rendered});
				});
			} else {
				options.push( { value: 0, label: 'Loading...' } )
			}

			return el( SelectControl,
				{
					label: 'Jump to ',
					options : options,
					onChange: function( post_id ) {
						window.location = "post.php?post="+post_id+"&action=edit";
					},
					value: props.metaValue,
				}
			);

		}

	);

	registerPlugin( 'switch-pages', {
		render: function() {
			return el( Fragment, {},
				el( PluginSidebarMoreMenuItem,
					{
						target: 'switch-page',
						icon: 'admin-settings',
					},
					'Switch Pages'
				),
				el( PluginSidebar,
					{
						name: 'switch-page',
						icon: 'admin-settings',
						title: 'Switch Pages',
					},
					el( PanelBody, {},
						// Field 1
						el( PostsDropdownControl,
							{
								metaKey: 'switch_pages_title',
								title : 'Jump to a post/page',
							}
						),
					)
				)
			);
		}
	} );

} )(
	window.wp.plugins,
	window.wp.editPost,
	window.wp.element,
	window.wp.components,
	window.wp.data,
	window.wp.compose
);
module.exports = {
    lang: 'en-US',
    title: 'codeigniter-shield',
    description: 'Shield is an authentication and authorization framework for CodeIgniter 4',
    dest: 'dist/docs',
    title: 'codeigniter-shield',
    base: '/docs/',
    port: 3000,
    plugins: [],
    devServer: {
        before: app => {
            // point `/docs` to VuePress dev server, configured above
            app.get('/docs', (req, res) => {
                res.redirect('http://localhost:3000/docs')
            })
        }
    },
   
    themeConfig: {
        logo: 'https://codeigniter.com/assets/icons/ci-footer.png',
        repo: 'lonnieezell/codeigniter-shield',
        // if your docs are not at the root of the repo:
        docsDir: 'docs',
        // if your docs are in a specific branch (defaults to 'master'):
        docsBranch: 'master',
        displayAllHeaders: true, // Default: false
		navbar: [
		  // NavbarItem
		  {
			text: 'Vision',
			link: 'vision.html',
		  },
          {
            text: 'Authentication',
            link: 'authentication.html',
          },
          {
            text: 'Authorization',
            link: 'authorization.html',
          },
          {
            text: 'Auth actions',
            link: 'auth_actions.html',
          },
           {
            text: 'Events',
            link: 'events.html',
          },
          {
            text: 'Testing',
            link: 'testing.html',
          },
		],
    }
}
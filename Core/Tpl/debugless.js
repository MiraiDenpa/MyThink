window.less = {
	//env: "development",
	env: "production",
	async: false,       // 异步加载导入的文件
	fileAsync: false,   // 使用文件协议访问页面时异步加载导入的文件
	poll: 5000,         // 在监视模式下，每两次请求之间的时间间隔（ms）
	functions: {},      // user functions, keyed by name
	dumpLineNumbers: "comments", // 或者"mediaQuery"，或者"all"
	relativeUrls: true,// 是否调整相对路径
	// 如果为false，则url已经是相对入口less文件的
	// entry less file
	rootpath: Think.PUBLIC_URL+'/', // 添加到每个url开始处的路径
	// entry less file
	path: Think.PUBLIC_URL+'/', //
	paths:[
		Think.PUBLIC_URL+'/'
	]
};

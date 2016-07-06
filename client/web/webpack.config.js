const path = require('path');
const webpack = require('webpack');
const ExtractTextPlugin = require('extract-text-webpack-plugin');

module.exports = {
    context: __dirname,
    entry: "./js/main.js",
    output: {
        path: path.join(__dirname, 'build'),
        filename: 'netric.js',
        publicPath: '/build/js/'
    },
    resolve: {
        extensions: ['', '.scss', '.js', '.jsx'],
        packageMains: ['browser', 'web', 'browserify', 'main', 'style'],
        alias: [
            {
                'netric': path.resolve(__dirname + './js')
            },
            {
                'localforage': 'localforage/dist/localforage.js'
            }
        ],
        modulesDirectories: [
            'node_modules',
            path.resolve(__dirname, './node_modules')
        ]
    },
    module: {
        loaders: [
            {
                test: /\.js$/,
                loader: 'babel',
                exclude: /(node_modules)/
            },
            {
                test: /\.(scss|css)$/,
                loader: ExtractTextPlugin.extract('style', 'css?sourceMap&modules&importLoaders=1&localIdentName=[name]__[local]___[hash:base64:5]!postcss!sass?sourceMap')
            },
            {
                test: /\.jsx$/,
                loader: 'babel',
                exclude: /(node_modules)/
            },
            {
                test: /\.scss/,
                loaders: ['style', 'css', 'sass']
            },
            {
                test: /localforage\/dist\/localforage.js/,
                loader: 'exports?localforage',
            }
        ],
        noParse: [
            /localforage\/dist\/localforage.js/
        ]
    },
    sassLoader: {
        data: '@import "' + path.resolve(__dirname, './../sass/base/_main.scss') + '";'
    },
    plugins: [
        new ExtractTextPlugin('docs.css', {allChunks: true})
    ]
};
const path = require('path');
const webpack = require('webpack');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const TransferWebpackPlugin = require('transfer-webpack-plugin');

module.exports = {
    context: __dirname,
    entry: "./js/main.js",
    output: {
        path: path.join(__dirname, 'build'),
        filename: 'js/netric.js',
        publicPath: '/build/',
        library: "netric"
    },
    resolve: {
        extensions: ['', '.scss', '.js', '.jsx'],
        packageMains: ['browser', 'web', 'browserify', 'main', 'style'],
        alias: [
            {
                netric: path.resolve(__dirname + './js')
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
                test: /localforage\/dist\/localforage.js/,
                loader: 'exports?localforage'
            },
            {
                test: /\.ttf$|\.eot$/,
                loader: 'file',
                query: {
                    name: 'fonts/[name].[ext]'
                },
                include: path.resolve(__dirname, "./fonts")
            }
        ],
        noParse: [
            /localforage\/dist\/localforage.js/
        ]
    },
    plugins: [
        new ExtractTextPlugin('css/netric.css', {allChunks: true}),
        new TransferWebpackPlugin([{
            from: 'img',
            to: 'img'
        },
        {
            from: 'fonts',
            to: 'fonts'
        }])
    ]
};
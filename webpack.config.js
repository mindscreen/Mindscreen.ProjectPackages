/* eslint-env node */
const path = require('path');
const webpack = require('webpack');
const TerserPlugin = require('terser-webpack-plugin');
const { VueLoaderPlugin } = require('vue-loader');

const config = {
    entry: './Resources/Private/App/src/index.ts',
    output: {
        path: path.resolve(__dirname, './Resources/Public/Build'),
        publicPath: '/_Resources/Static/Packages/Mindscreen.ProjectPackages/Build/',
        filename: 'build.js',
        assetModuleFilename: 'assets/[name][ext]?[hash]',
    },
    mode: 'development',
    module: {
        rules: [
            {
                test: /\.vue$/,
                loader: 'vue-loader',
            },
            {
                test: /\.ts$/,
                loader: 'ts-loader',
                exclude: /node_modules/,
                options: {
                    appendTsSuffixTo: [ /\.vue$/ ],
                },
            },
            {
                test: /\.(png|jpg|gif|svg)$/,
                type: 'asset/resource',
            },
            {
                test: /\.scss$/,
                use: [
                    'vue-style-loader',
                    'css-loader',
                    {
                        loader: 'sass-loader',
                        options: {
                            implementation: require('sass'),
                        },
                    },
                ],
            },
        ],
    },
    plugins: [
        new VueLoaderPlugin(),
    ],
    resolve: {
        extensions: [ '.ts', '.js', '.vue', '.json' ],
        alias: {
            'vue$': 'vue/dist/vue.esm.js',
        },
    },
    devServer: {
        historyApiFallback: true,
        noInfo: true,
    },
    performance: {
        hints: false,
    },
    devtool: 'eval-source-map',
};

if (process.env.NODE_ENV === 'production') {
    config.devtool = 'source-map';
    config.mode = 'production';

    config.plugins = (config.plugins || []).concat([
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: '"production"',
            },
        }),
    ]);

    config.optimization = {
        minimize: true,
        minimizer: [ new TerserPlugin() ],
    };
}

module.exports = config;

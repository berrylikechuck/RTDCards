var path = require('path');
var webpack = require('webpack');

module.exports = {
    entry: './App.js',
    output: {
        path: __dirname,
        filename: 'bundle.js'
    },
    module: {
        loaders: [{
            test: /.jsx?$/,
            loader: 'babel-loader',
            exclude: /node_modules/,
            query: {
                presets: [
                    ['env',{
                        'targets': {
                            'browsers': ['last 2 versions', '> 2%']
                        }
                    }],
                    'react',
                    'stage-0'
                ]
            }
        }]
    }
}
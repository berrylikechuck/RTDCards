const path = require("path");
const HtmlWebpackPlugin = require("html-webpack-plugin");

module.exports = {
  entry: path.join(__dirname, "js/src", "App.js"),
  output: {
    path: path.join(__dirname, "js/build"),
    filename: "index.bundle.js"
  },
  mode: process.env.NODE_ENV || "development",
  resolve: { modules: [path.resolve(__dirname, "js/src"), "node_modules"] },
  devServer: { contentBase: path.join(__dirname, "js/src") },
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        use: ["babel-loader"],
      },
      {
        test: /\.(css|scss)$/,
        use: ["style-loader", "css-loader"],
      },
      {
        test: /\.(jpg|jpeg|png|gif|mp3|svg)$/,
        use: ["file-loader"],
      },
    ],
  }
};
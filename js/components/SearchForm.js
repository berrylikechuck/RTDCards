import React, { Component } from 'react';
import axios from 'axios';
import Select from 'react-select';

export default class SearchForm extends Component {

    state = {
            terms: [],
            value: ''
    };

    componentDidMount() {
        this.performSearch();
    }

    performSearch = (query) => {
        axios.get('/data/terms', {
                params: {
                    q: query
                }
            })
            .then(response => {
                this.setState({
                    terms: response.data
                });
            })
            .catch(error => {
                console.log('Error fetching and parsing data', error);
            });
    }

    handleSubmit = (e) => {
        e.preventDefault();
        this.props.onSearch(this.state.value);
        e.currentTarget.reset();
    }

    handleSelectChange = (value) => {
        this.setState({ value });
    }

    render() {
        return (
            <form className="search-form" onSubmit={this.handleSubmit}>
                <label htmlFor="states-autocomplete">Choose a tag</label>
                <Select
                    closeOnSelect={true}
                    disabled={false}
                    multi={true}
                    onChange={this.handleSelectChange}
                    options={this.state.terms}
                    placeholder="Select keyword(s)"
                    removeSelected={true}
                    rtl={false}
                    simpleValue={true}
                    value={this.state.value}
                    clearable={false}
                />
                <button type="submit" id="submit" className="search-button"><i className="material-icons icn-search">search</i></button>
            </form>
        );
    }
}

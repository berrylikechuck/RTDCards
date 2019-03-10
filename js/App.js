import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import axios from 'axios';

import SearchForm from './SearchForm';
import CoreList from './CoreList';

class App extends Component{

    state = {
        cards: [],
        currentTid: 0,
        tidList: []
    };

    componentDidMount() {
        this.performSearch();
    }

    performSearch = (query) => {
        axios.get('/data/cards', {
                params: {
                    q: query
                }
            })
            .then(response => {
                this.setState({
                    cards: response.data
                });
            })
            .catch(error => {
                console.log('Error fetching and parsing data', error);
            });
    }

    filterCores = (tids) => {
        if(!tids){
            this.setState({
                tidList: []
            });
            return;
        }
        this.setState({
            tidList: tids.split(',')
        });
    }

    doesCoreContainTid = (coreTids) => {
        return this.state.tidList.some(v => coreTids.includes(v));
    }

    render() {

        return (
            <div>
                <SearchForm onSearch={this.filterCores} />
                <div className="coresList">
                    <CoreList data={this.state.cards.filter( core => {
                        return (this.state.tidList.length == 0 || this.doesCoreContainTid(core.tids))
                    })} />
                </div>
            </div>
        );

    }

}

ReactDOM.render(
    <App />,
    document.getElementById('cards-app')
);
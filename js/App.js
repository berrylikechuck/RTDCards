import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import axios from 'axios';

import SearchForm from './components/SearchForm';
import CardList from './components/CardList';

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

    filterCards = (tids) => {
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

    doesCardContainTid = (coreTids) => {
        return this.state.tidList.some(v => coreTids.includes(v));
    }

    render() {

        return (
            <div>
                <SearchForm onSearch={this.filterCards} />
                <div className="cardsList">
                    <CardList data={this.state.cards.filter( card => {
                        return (this.state.tidList.length == 0 || this.doesCardContainTid(card.tids))
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

import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import classNames from 'classnames';
import cloneDeep from 'clone-deep';
import date from 'date-and-time';
import axios from 'axios';

function Game(props) {
    console.log(props);

    const [ended, setEnded] = useState(props.ended ? props.ended : false);
    const [loading, setLoading] = useState(true);
    const [stepped, setStepped] = useState(false);
    const [infos, setInfos] = useState([]);
    const [givedUp, setGivedUp] = useState(false);
    const [gameMap, setGameMap] = useState([
        [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
    ]);

    if (loading) {
        axios.get(`/games/${props.gameId}/fetch`)
        .then(function (resp) {
            setLoading(false);
            setGameMap(resp.data.map);
            let temp = [];
            for (let step of resp.data.steps) {
                console.log(step);
                temp.push({
                    time: new Date(step.created_at),
                    message: `${step.player.name} a(z) ${step.row}. sor ${step.col}. mezőjére lépett`,
                    error: false,
                });
            }
            if (temp) {
                setInfos(temp);
            }
        }).catch(function (err) {
            setLoading(false);
            addInfoEntry(err.response.data.error, true);
        });
    }

    function handleGiveUp() {
        setGivedUp(true);
    }

    function addInfoEntry(message, isError = false) {
        const transformErrorMessages = {
            'PlayerNotInGame': 'Nem vagy játékban',
            'InvalidGameStatus': 'A játék még nincs elindítva',
            'InvalidPlayer': 'Nem vagy benne ebben a játékban',
            'DuplicatedStep': 'Nem léphetsz kétszer egymás után',
            'InvalidPosition': 'Érvénytelen pozícióra akartál lépni',
            'UnavailablePosition': 'Ez a mező már foglalt',
            'SaveFailed': 'Mentési hiba',
        }
        const key = Object.keys(transformErrorMessages).find(key => key === message);
        setInfos([...infos, {
            time: new Date(),
            message: key ? transformErrorMessages[key] : message,
            error: key ? true : isError,
        }]);
    }

    function handleClick(row, col) {
        if (givedUp || stepped || loading) return;
        if (gameMap[row-1][col-1] !== 0) {
            addInfoEntry('UnavailablePosition');
            return;
        }
        setStepped(true);
        axios.post(`/games/${props.gameId}/step`, {
            row,
            col,
        }).then(function (resp) {
            /*let gameMapClone = cloneDeep(gameMap);
            gameMapClone[row-1][col-1] = 1;
            setGameMap(gameMapClone);*/
            setGameMap(resp.data.map);
            let temp = [];
            for (let step of resp.data.steps) {
                console.log(step);
                temp.push({
                    time: new Date(step.created_at),
                    message: `${step.player.name} a(z) ${step.row}. sor ${step.col}. mezőjére lépett`,
                    error: false,
                });
            }
            if (temp) {
                setInfos(temp);
            }
            setStepped(false);
        }).catch(function (err) {
            addInfoEntry(err.response.data.error, true);
            setStepped(false);
        });
    }

    return (
        loading === true ?
        <div className="text-center">
            <div className="spinner-border" role="status">
            </div>
            <p>Játék betöltése...</p>
        </div>
        :
        <div className="row">
            <div className="col-12 col-lg-8">
                <h1 className="mb-4">Játékterület</h1>
                <div className="container w-md-50 mb-3">
                    <div className="row justify-content-center">{
                        gameMap.map((row, row_idx) => row.map((col, col_idx) => {
                            let classes = classNames('col-1 p-0 square', {
                                'square-x': col === 1,
                                'square-o': col === 6,
                                'disabled': givedUp || ended,
                            })
                            return <div onClick={() => handleClick(row_idx+1, col_idx+1)} key={10*row_idx+col_idx+1} className={classes}></div>
                        }))
                    }</div>
                </div>
                <div className="row">
                    <div className="col-6">
                        <button disabled={givedUp || ended || loading} className="btn btn-lg btn-danger" onClick={handleGiveUp}>Játék feladása</button>
                    </div>
                    <div className="col-6">{
                        stepped === true ?
                            <div className="text-right">
                                <div className="spinner-border" role="status">
                                </div>
                            </div>
                        : <></>
                    }</div>
                </div>
            </div>
            <div className="col-12 col-lg-4">
                <h1 className="mb-4">Infók</h1>
                <div className="overflow-auto">{
                    infos.map((info, idx) => <p key={idx} className="font-weight-bold">
                        <span className="text-success mr-2">[{date.format(info.time, 'HH:mm:ss')}]</span>
                        <span>{info.message}</span>
                    </p>)
                }</div>
            </div>
        </div>
    );
}

export default Game;

const game = document.querySelector('#game');
if (game) {
    const props = Object.assign({}, game.dataset)
    ReactDOM.render(<Game {...props} />, game);
}

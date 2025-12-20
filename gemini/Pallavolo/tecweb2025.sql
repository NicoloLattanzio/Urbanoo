USE ansgreva;

DROP TABLE IF EXISTS atleti;

CREATE TABLE atleti (
                        ID               INT AUTO_INCREMENT PRIMARY KEY,
                        nome             VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL,
                        capitano         TINYINT(1) NOT NULL,
                        dataNascita      VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL,
                        luogo            VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL,
                        squadra          VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL,
                        ruolo            VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL,
                        altezza          INT NOT NULL,
                        maglia           INT NOT NULL,
                        magliaNazionale  INT NOT NULL,
                        punti            INT NOT NULL,
                        riconoscimenti   LONGTEXT COLLATE utf8_unicode_ci,
                        note             LONGTEXT COLLATE utf8_unicode_ci,
                        immagine         VARCHAR(100) COLLATE utf8_unicode_ci,
                        genere           TINYINT(1) NOT NULL
) ENGINE=InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- INSERT DONNE
-- -----------------------------------------------------

INSERT INTO atleti (
    nome, capitano, dataNascita, luogo, squadra, ruolo,
    altezza, maglia, magliaNazionale, punti,
    riconoscimenti, note, immagine, genere
) VALUES
      (
          'Anna Danesi', 1, '20/04/1996', 'Brescia', 'Pro Victoria', 'Centrale',
          196, 11, 11, 43,
          '<ul>
              <li>Miglior centrale nel Campionato mondiale U20 2015.</li>
              <li>Miglior centrale nel Campionato europeo 2021.</li>
              <li>Miglior centrale nel Mondiale 2022.</li>
              <li>Miglior centrale ai Giochi Olimpici 2024.</li>
          </ul>',
          '',
          '../images/danesi.jpg',
          1
      ),
      (
          'Carlotta Cambi', 0, '28/05/1996', 'San Miniato', 'Pinerolo', 'Palleggiatrice',
          176, 3, 3, 12,
          '',
          'Ha fatto parte della squadra AGIL nelle stagioni 2016/17 e 2022/23. Dal 2023/24 gioca per Pinerolo.',
          '../images/cambi.jpg',
          1
      ),
      (
          'Alessia Orro', 0, '18/07/1998', 'Oristano', 'Pro Victoria', 'Palleggiatrice',
          180, 8, 8, 105,
          '<ul>
              <li>MVP Coppa CEV 2021</li>
              <li>Miglior palleggiatrice VNL 2022</li>
              <li>Miglior palleggiatrice VNL 2024</li>
              <li>Miglior palleggiatrice alle Olimpiadi 2024</li>
          </ul>',
          '',
          '../images/orro.jpg',
          1
      ),
      (
          'Gaia Giovannini', 0, '17/12/2001', 'Bologna', 'Megavolley', 'Schiacciatrice',
          182, 17, 27, 10,
          '<ul>
              <li>Miglior Sportiva 2024</li>
              <li>Collana d\'Oro al merito sportivo</li>
          </ul>',
          '',
          '../images/giovannini.jpeg',
          1
      ),
      (
          'Stella Nervini', 0, '10/09/2003', 'Milano', 'Chieri 76', 'Schiacciatrice',
          184, 11, 16, 54,
          '<ul>
              <li>Miglior Schiacciatrice Mondiale U21 2023</li>
              <li>MVP Serie A1</li>
          </ul>',
          '',
          '../images/nervini.jpeg',
          1
      ),
      (
          'Myriam Sylla', 0, '08/01/1995', 'Palermo', 'Pro Victoria', 'Schiacciatrice',
          184, 17, 17, 60,
          '<ul>
              <li>Miglior schiacciatrice Europeo 2021</li>
              <li>Miglior schiacciatrice Mondiale 2022</li>
              <li>Miglior schiacciatrice VNL 2024</li>
              <li>Miglior schiacciatrice Olimpiadi 2024</li>
          </ul>',
          '',
          '../images/sylla.jpg',
          1
      ),
      (
          'Loveth Omoruyi', 0, '25/08/2002', 'Lodi', 'Reale Mutua Fenera Chieri', 'Schiacciatrice',
          184, 2, 21, 60,
          '<ul>
              <li>Miglior schiacciatrice Mondiale U20 2021</li>
              <li>Miglior schiacciatrice Mondiale U18 2019</li>
          </ul>',
          '',
          '../images/omoruyi.jpg',
          1
      ),
      (
          'Sarah Fahr', 0, '12/09/2001', 'Kulmbach', 'Imoco', 'Centrale',
          192, 19, 19, 36,
          '<ul>
              <li>Miglior centrale Europeo U19 2018</li>
              <li>Miglior centrale Volley Master 2019</li>
              <li>Miglior centrale VNL 2024</li>
              <li>MVP Supercoppa Italiana 2024</li>
          </ul>',
          '',
          '../images/fahr.jpg',
          1
      ),
      (
          'Benedetta Sartori', 0, '14/04/2001', 'Milano', 'Pro Victoria', 'Centrale',
          187, 10, 10, 20,
          '<ul><li>Oro Festival Olimpico Gioventù Europea 2017</li></ul>',
          '',
          '../images/sartori.jpg',
          1
      ),
      (
          'Yasmina Akrari', 0, '31/08/1993', 'Torino', 'Pinerolo', 'Centrale',
          185, 18, 25, 55,
          '<ul><li>Oro Festival Olimpico Gioventù Europea 2017</li></ul>',
          '',
          '../images/sartori.jpg',
          1
      ),
      (
          'Paola Ogechi Egonu', 0, '10/12/1998', 'Cittadella', 'Pro Victoria', 'Opposto',
          190, 18, 18, 110,
          '<ul>
              <li>MVP Serie A1 2022</li>
              <li>MVP VNL 2024</li>
              <li>MVP Champions League 2024</li>
              <li>MVP Olimpiadi 2024</li>
          </ul>',
          '',
          '../images/Egonu.jpg',
          1
      ),
      (
          'Ekaterina Antropova', 0, '19/09/2003', 'Akureyri', 'Savino Del Bene', 'Opposto',
          202, 24, 24, 54,
          '<ul><li>MVP CEV Challenge Cup 2021–22</li></ul>',
          '',
          '../images/Antropova.jpg',
          1
      ),
      (
          'Monica De Gennaro', 0, '08/01/1987', 'Piano di Sorrento', 'Imoco', 'Libero',
          173, 10, 10, 64,
          '<ul><li>MVP Serie A1 2019</li></ul>',
          'Inserita tra le 100 donne italiane Forbes 2025.',
          '../images/DeGennaro.jpg',
          1
      ),
      (
          'Ilaria Spirito', 0, '20/02/1994', 'Albisola Superiore', 'Chieri 76', 'Libero',
          174, 5, 5, 0,
          '<ul><li>Vincitrice Coppa CEV con Chieri</li></ul>',
          '',
          '../images/spirito.jpg',
          1
      );


-- -----------------------------------------------------
-- INSERT UOMINI
-- -----------------------------------------------------

INSERT INTO atleti (
    nome, capitano, dataNascita, luogo, squadra, ruolo,
    altezza, maglia, magliaNazionale, punti,
    riconoscimenti, note, immagine, genere
) VALUES
      (
          'Simone Giannelli', 1, '09/08/1996', 'Bolzano', 'Sir Safety Susa Perugia',
          'Palleggiatore', 200, 6, 6, 872,
          '<ul>
              <li>Premio G. Badiali 2015–2017</li>
              <li>Miglior palleggiatore Europei 2015</li>
              <li>Miglior palleggiatore World League 2016</li>
              <li>MVP Europei 2021</li>
              <li>MVP Mondiali 2022</li>
          </ul>',
          'Ha giocato in Itas Trentino fino al 2021.',
          '../images/giannelli.jpg',
          0
      ),
      (
          'Riccardo Sbertoli', 0, '23/05/1998', 'Milano', 'Itas Trentino',
          'Palleggiatore', 190, 6, 8, 393,
          '',
          'Ex giocatore Allianz Milano fino al 2021.',
          '../images/sbertoli.jpg',
          0
      ),
      (
          'Alessandro Michieletto', 0, '05/12/2001', 'Desenzano del Garda',
          'Itas Trentino', 'Schiacciatore', 211, 5, 5, 960,
          '',
          '',
          '../images/michieletto.jpg',
          0
      ),
      (
          'Mattia Bottolo', 0, '03/01/2000', 'Bassano del Grappa',
          'Cucine Lube Civitanova', 'Schiacciatore', 196, 21, 12, 87,
          '',
          'Contratto quinquennale con Lube dal 2022.',
          '../images/bottolo.jpg',
          0
      ),
      (
          'Luca Porro', 0, '09/05/2004', 'Genova', 'Pallavolo Padova',
          'Schiacciatore', 196, 17, 31, 303,
          '<ul>
              <li>Miglior schiacciatore Europeo U18 2020</li>
              <li>Premio Badiali U23 2023–24</li>
          </ul>',
          '',
          '../images/porro.jpg',
          0
      ),
      (
          'Francesco Sani', 0, '16/07/2002', 'Greenbrae', 'Rana Verona',
          'Schiacciatore', 184, 203, 11, 9,
          'Ha ottenuto riconoscimento FIVB per formazione italiana.',
          '',
          '../images/sani.jpg',
          0
      ),
      (
          'Simone Anzani', 0, '24/02/1992', 'Como', 'Cucine Lube Civitanova',
          'Centrale', 203, 17, 17, 2276,
          '',
          '',
          '../images/anzani.jpg',
          0
      ),
      (
          'Gianluca Galassi', 0, '24/07/1997', 'Trento', 'Vero Volley Monza',
          'Centrale', 201, 11, 14, 1144,
          '<ul><li>Miglior centrale Mondiali 2022</li></ul>',
          '',
          '../images/galassi.jpg',
          0
      ),
      (
          'Giovanni Maria Gargiulo', 0, '01/01/1999', 'Sorrento',
          'Cucine Lube Civitanova', 'Centrale', 208, 2, 3, 172,
          '<ul><li>Vincitore Coppa Italia 2024–25</li></ul>',
          '',
          '../images/gargiulo.jpg',
          0
      ),
      (
          'Roberto Russo', 0, '23/02/1997', 'Palermo', 'Sir Susa Vim Perugia',
          'Centrale', 205, 18, 19, 172,
          '',
          'Considerato tra i migliori centrali italiani.',
          '../images/russo.jpg',
          0
      ),
      (
          'Yuri Romanò', 0, '26/07/1997', 'Monza',
          'Gas Sales Bluenergy Piacenza', 'Opposto',
          203, 17, 16, 1865,
          '',
          '',
          '../images/romano.jpg',
          0
      ),
      (
          'Kamil Rychlicki', 0, '01/11/1996', 'Ettelbruck',
          'Itas Trentino', 'Opposto', 204, 11, 11, 517,
          '<ul><li>Miglior schiacciatore Campionato Piccoli Stati</li></ul>',
          '',
          '../images/rychlicki.jpg',
          0
      ),
      (
          'Fabio Balaso', 0, '20/10/1995', 'Camposampiero',
          'Cucine Lube Civitanova', 'Libero', 178, 7, 7, 1606,
          '<ul>
              <li>Premio Badiali 2013–14</li>
              <li>Miglior libero Mondiali 2022</li>
          </ul>',
          '',
          '../images/balaso.jpg',
          0
      ),
      (
          'Domenico Pace', 0, '02/08/2000', 'Castellana Grotte',
          'Cisterna Volley', 'Libero', 180, 8, 8, 1606,
          '<ul>
              <li>Premio Badiali 2013–14</li>
              <li>Miglior libero Mondiali 2022</li>
          </ul>',
          '',
          '../images/pace.jpg',
          0
      );

<?php

class Migration_add_tree_types extends CI_Migration {

    public function up() {
        
        $CsvString = "Abies amabilis,Pacific silver fir
Abies balsamea,Balsam fir
Abies bracteata,bristlecone fir
Abies concolor,white fir
Abies flinckii,Jalisco fir
Abies grandis,grand fir
Abies lasiocarpa,subalpine fir
Abies magnifica,red fir
Abies nordmanniana,Nordmann Fir
Abies pinsapo,Spanish Fir
Abies procera,noble fir
Acer circinatum,Vine maple
Acer floridanum,Florida maple
Acer glabrum,Douglas maple
Acer glabrum,Rocky Mountain maple
Acer grandidentatum,bigtooth maple
Acer grinnala,Amur maple
Acer leucoderme,whitebark maple
Acer macrophyllum,Bigleaf maple
Acer negundo,Manitoba maple
Acer nigrum,black maple
Acer pensylvanicum,striped maple
Acer palmatum,Japanese maple
Acer platanoides,Norway Maple
Acer pseudoplatanus,Sycamore maple
Acer rubrum,red maple
Acer saccharinum,silver maple
Acer saccharum,sugar maple
Acer spicatum,mountain maple
Acoelorrhaphe wrightii,Paurotis palm
Aesculus californica,California buckeye
Aesculus flava,yellow buckeye
Aesculus glabra,Ohio buckeye
Aesculus hippocastanum,Horsechestnut
Aesculus pavia,red buckeye
Ailanthus altissima,Tree of heaven
Alfaroa mexicana,
Alibertia edulis,
Alnus acuminata,
Alnus glutinosa,European black alder
Alnus incana,Mountain alder
Alnus jorullensis,Mexican alder
Alnus maritima,seaside alder
Alnus oblongifolia,Arizona alder
Alnus rhombifolia,white alder
Alnus rubra,red alder
Alnus serrulata,hazel alder
Alnus viridis,Sitka alder
Alstonia longifolia,
Amelanchier alnifolia,Saskatoon berry
Amelanchier arborea,downy serviceberry
Amelanchier canadensis,Canadian serviceberry
Amelanchier florida,Pacific serviceberry
Amelanchier laevis,Smooth serviceberry
Amelanchier sanguinea,Roundleaf serviceberry
Amelanchier bartramiana,Mountain serviceberry
Amyris elemifera,sea torchwood
Annona glabra,pond apple
Annona purpurea,soncoya
Aralia elata,Japanese angelica-tree
Aralia spinosa,devil's walking stick
Arbutus arizonica,Arizona madrone
Arbutus xalapensis,Texas madrone
Arucaria arucana,Monkey-Puzzle
Asimina parviflora,smallflower pawpaw
Asimina triloba,papaw
Aspidosperma spruceanum,
Astianthus,achuchil
Attalea butyracea,
Bactris major,
Bauhinia lunarioides,Texasplume
Betula alleghaniensis,yellow birch
Betula cordifolia,mountain paper birch
Betula x caerulea,Blueleaf birch
Betula lenta,Cherry birch
Betula kenaica,Kenai Birch
Betula neoalaskana,Alaska birch
Betula nigra,river birch
Betula occidentalis,Water birch
Betula pendula,European white birch
Betula papyrifera,paper birch
Betula populifolia,grey birch
Betula uber,Virginia round-leaf birch
Brahea armata,Mexican blue palm
Brosimum alicastrum,breadnut
Bursera graveolens,palo santo
Bursera simaruba,gumbo-limbo
Byrsonima crassifolia,changunga
Calocedrus decurrens,California incense-cedar
Capparis cynophallophora,Jamaican caper
Caragana arborescens,Siberian pea-tree
Carpinus caroliniana,Blue-beech
Carya aquatica,bitter pecan
Carya cordiformis,bitternut hickory
Carya floridana,scrub hickory
Carya glabra,red hickory
Carya laciniosa,shellbark hickory
Carya myristiciformis,nutmeg hickory
Carya ovalis,red hickory
Carya ovata,shagbark hickory
Carya tomentosa,mockernut hickory
Cassia grandis,pink shower tree
Castanea dentata,American chestnut
Castanea mollissima,Chinese chestnut
Castanea ozarkensis,Ozark chinkapin
Castanea pumila,Allegheny chinquapin
Catalpa speciosa,Northern catalpa
Catalpa bignonioides,southern catalpa
Cedrela odorata,Spanish cedar
Cedrela tonduzii,West Indian cedar
Cedrus atlantica,Atlas cedar
Cedrus deodara,Deodar cedar
Cedrus libani,Cedar-of-Lebanon
Ceiba pentandra,Kapok
Celtis laevigata,sugarberry
Celtis occidentalis,Hackberry
Celtis reticulata,netleaf hackberry
Celtis tenuifolia,dwarf hackberry
Cercis canadensis,eastern redbud
Cercidiphyllum japonicum,Katsura-tree
Chamaecyparis lawsoniana,Lawson-Cypress
Chamaecyparis nootkatensis,Yellow-cedar
Chamaecyparis obtusa,Hinoki-Cypress
Chamaecyparis pisifera,Sawara-Cypress
Chamaecyparis thyoides,Atlantic white cedar
Chilopsis linearis,desert willow
Chionanthus virginicus,white fringetree
Chrysolepis,chinquapin
Citharexylum spinosum,Florida fiddlewood
Cladrastis kentukea,Kentucky yellowwood
Cladastris lutea,Yellow-wood
Cliftonia,buck-wheat tree
Coccoloba diversifolia,pigeonplum
Coccothrinax argentata,Florida silver palm
Coccothrinax readii,Mexican silver palm
Cochlospermum vitifolium,
Cojoba arborea,
Colubrina elliptica,soldierwood
Condalia globosa,bitter condalia
Conocarpus erectus,buttonwood
Cordia alliodora,Spanish elm
Cordia boissieri,Texas wild olive
Cordia sebestena,Geiger tree
Cornus × arnoldiana,Arnold dogwood
Cornus alternifolia,alternate-leaf dogwood
Cornus amomum,silky dogwood
Cornus drummondii,roughleaf dogwood
Cornus florida,Eastern flowering dogwood
Cornus kousa,kousa dogwood
Cornus mas,Cornelian-Cherry
Cornus nuttallii,Western flowering dogwood
Cornus racemosa,northern swamp dogwood
Corylus cornuta,Beaked hazel
Corylus americana,American hazel
Corylus colurna,Turkish hazel
Corylus avellana,European filbert
Crataegus ambitiosa,Grand Rapids hawthorn
Crataegus anamesa,Fort Bend hawthorn
Crataegus arborea,Montgomery hawthorn
Crataegus austromontana,Valley Head hawthorn
Crataegus berberifolia,barberry hawthorn
Crataegus calpodendron,Pear hawthorn
Crataegus coccinea,scarlet hawthorn
Crataegus columbiana,Columbia hawthorn
Crataegus crus-galli,cockspur hawthorn
Crataegus chrysocarpa,Fireberry hawthorn
Crataegus douglasii ,Black hawthorn
Crataegus flabellata,fanleaf hawthorn
Crataegus intricata,Copenhagen hawthorn
Crataegus macrosperma,bigfruit hawthorn
Crataegus mollis,downy hawthorn
Crataegus persimilis,plumleaf hawthorn
Crataegus phaenopyrum,Washington hawthorn
Crataegus pruinosa,frosted hawthorn
Crataegus punctata,dotted hawthorn
Crataegus succulenta,fleshy hawthorn
Crataegus uniflora,one-flowered hawthorn
Crataegus viridis,green hawthorn
Crescentia alata,Mexican calabash
Crescentia cujete,calabash tree
Cryptomeria japonica,Japanese-Cedar
Cupressus abramsiana,Santa Cruz cypress
Cupressus arizonica,Arizona cypress
Cupressus arizonica var. glabra,Arizona smooth bark cypress
Cupressus bakeri,Baker cypress
Cupressus forbesii,Tecate cypress
Cupressus goveniana,Californian cypress
Cupressus lusitanica,Mexican white cedar
Cupressus macnabiana,MacNab cypress
Cupressus macrocarpa,Monterey cypress
Cupressus nevadensis,Paiute cypress
Cupressus nootkatensis,Nootka cypress
Cupressus pigmaea,Mendocino cypress
Cupressus sargentii,Sargent's cypress
Cupressus stephensonii,Cuyamaca cypress
Cyrilla,swamp cyrilla
Dermatophyllum secundiflorum,Texas mountain laurel
Desmoncus,Spiny palm
Diospyros nigra,black sapote
Diospyros texana,Texas persimmon
Diospyros virginiana,American persimmon
Dodonaea viscosa,hopbush
Ebenopsis ebano,Texas ebony
Ehretia anacua,sandpaper tree
Elaeagnus angustifolia,Russian-olive
Erythrina coralloides,flame coral tree
Eysenhardtia orthocarpa,kidneywood
Eysenhardtia texana,Texas kidneywood
Euonymus atropurpureus,Burning-bush euonymus
Euonymus europaeus,European euonymus
Euonymus fortunei,Winter-creeper euonymus
Euonymus alatus ,Winged euonymus
Fagus grandifolia,American beech
Ficus americana,West Indian laurel fig
Ficus aurea,Florida strangler fig
Ficus citrifolia,shortleaf fig
Ficus maxima,Amate
Ficus obtusifolia,
Ficus pertusa,
Ficus trigonata,
Ficus yoponensis,
Franklinia alatamaha,Franklin tree
Fraxinus americana,white ash
Fraxinus anomala,single-leaf ash
Fraxinus caroliniana,swamp ash
Fraxinus dipetala,California ash
Fraxinus excelsior,European ash
Fraxinus latifolia,Oregon ash
Fraxinus nigra,black ash
Fraxinus pennsylvanica,green ash
Fraxinus profunda,pumpkin ash
Fraxinus quadrangulata,blue ash
Gaussia gomez-pompae,
Geonoma undata,
Ginkgo biloba,Ginkgo
Gleditsia aquatica,water locust
Gleditsia triacanthos,Honey-locust
Gliricidia sepium,Gliricidia
Gordonia lasianthus,loblolly-bay
Guaiacum angustifolium,Texas guaiacum
Guaiacum sanctum,holywood
Guazuma ulmifolia,West Indian elm
Gymnanthes lucida,shiny oysterwood
Gymnocladus dioicus,Kentucky coffeetree
Halesia,silverbell
Halesia diptera,two-wing silverbell
Halesia monticola,mountain silverbell
Halesia tetraptera,common silverbell
Hamamelis virginiana,witch-hazel
Handroanthus impetiginosus,pink trumpet tree
Havardia albicans,chucum
Heliocarpus americanus,majaguillo
Hippophae rhamnoides,Sea-buckthorn
Ilex amelanchier,swamp holly
Ilex aquifolium,English holly
Ilex cassine,dahoon holly
Ilex decidua,swamp holly
Ilex laevigata,smooth winterberry
Ilex montana,mountain winterberry
Ilex opaca,american holly
Ilex verticillata,common winterberry
Ilex vomitoria,yaupon holly
Itea virginica,
Jacaranda,Jacaranda tree
Jacaratia mexicana,bonete
Jatropha curcas,physic nut
Juglans californica,California black walnut
Juglans cinerea,butternut
Juglans hindsii,Northern California walnut
Juglans major,Arizona walnut
Juglans microcarpa,Texas walnut
Juglans nigra,black walnut
Juniperus ashei,Ashe juniper
Juniperus californica,California juniper
Juniperus chinensis,Chinese juniper
Juniperus coahuilensis,redberry juniper
Juniperus communis,Common juniper
Juniperus deppeana,alligator juniper
Juniperus flaccida,drooping juniper
Juniperus grandis,Sierra juniper
Juniperus horizontalis,Creeping juniper
Juniperus monosperma,one-seed juniper
Juniperus occidentalis,western juniper
Juniperus osteosperma,Utah juniper
Juniperus sabina,Savin juniper
Juniperus scopulorum,Rocky Mountain juniper
Juniperus virginiana,eastern redcedar
Kalmia latifolia,mountain laurel
Krugiodendron,black ironwood
Laburnum anagyroides,Goldenchain tree
Larix decidua,European larch
Larix gmelinii,Dahurian larch
Larix kaempferi,Japanese larch
Larix laricina,tamarack
Larix lyallii,subalpine larch
Larix occidentalis,western larch
Larix sibirica,Siberian Larch
Leitneria floridana,corkwood
Leucaena leucocephala,white leadtree
Leucothrinax morrisii,Key thatch palm
Libidibia coriaria,Divi-divi
Liquidambar styraciflua,sweetgum
Liriodendron tulipifera,tulip tree
Lyonia ligustrina,maleberry
Lyonothamnus floribundus,Catalina ironwood
Lysiloma latisiliquum,false tamarind
Maclura pomifera,Osage orange
Maclura tinctoria,old fustic
Magnolia acuminata,cucumber tree
Magnolia dealbata,cloudforest magnolia
Magnolia fraseri,Fraser magnolia
Magnolia grandiflora,southern magnolia
Magnolia guatemalensis,Honduran magnolia
Magnolia macrophylla,bigleaf magnolia
Magnolia virginiana,umbrella magnolia
Magnolia x soulangea,Saucer magnolia
Malus coronaria,Wild crab apple
Malus fusca,Pacific crab apple
Malus sylvestris,Common apple
Malus baccata,Siberian crab apple
Mespilus canescens,Stern's medlar
Metasequoia glyptostroboides,Dawn redwood
Metopium toxiferum,Florida poisontree
Morus rubra,Red mulberry
Morus alba,white mulberry
Muntingia,calabur tree
Myrcianthes fragrans,twinberry
Myrica californica,parcific bayberry
Nemopanthus mucronatus,Mountain-holly
Notholithocarpus,tanoak
Nyssa aquatica,water tupelo
Nyssa biflora,swamp tupelo
Nyssa ogeche,white tupelo
Nyssa sylvatica,black tupelo
Ochroma pyramidale,balsa tree
Oreopanax peltatus,desert ironwood
Ostrya virginiana,Ironwood (Hornbeam)
Oxydendrum,sorrel tree
Pachira aquatica,Malabar chestnut
Parkinsonia aculeata,palo verde
Parkinsonia florida,blue palo verde
Parkinsonia microphylla,yellow paloverde
Parkinsonia texana,Texas palo verde
Persea borbonia,redbay
Persea schiedeana,swampbay
Phellodendron amurense,Amur corktree
Picea abies,Norway spruce
Picea breweriana,Brewer spruce
Picea engelmannii,Engelmann spruce
Picea glauca,White Spruce
Picea mariana,black spruce
Picea omorika,Serbian spruce
Picea pungens,Colorado spruce
Picea rubens,red spruce
Picea sitchensis,Sitka spruce
Pinu mugo,Mugho Pine
Pinus albicaulis,Whitebark Pine
Pinus aristata,Bristlecone pine
Pinus arizonica,Arizona pine
Pinus attenuata,knobcone pine
Pinus ayacahuite,Mexican white pine
Pinus balfouriana,foxtail pine
Pinus banksiana,Jack pine
Pinus cembroides,pinyon pine
Pinus clausa,sand pine
Pinus contorta,lodgepole pine
Pinus coulteri,Coulter pine
Pinus echinata,shortleaf pine
Pinus edulis,Colorado pinyon
Pinus elliottii,slash pine
Pinus engelmannii,Apache pine
Pinus flexilis,Limber Pine
Pinus glabra,spruce pine
Pinus greggii,Gregg's pine
Pinus jeffreyi,Jeffrey pine
Pinus johannis,Johann's pine
Pinus lambertiana,sugar pine
Pinus leiophylla,Chihuahua pine
Pinus longaeva,Great Basin bristlecone pine
Pinus lumholtzii,Lumholtz's pine
Pinus monticola,Western White Pine
Pinus muricata,single-leaf pinyon
Pinus nigra,Austrian pine
Pinus pinceana,weeping pinyon
Pinus ponderosa,Ponderosa pine
Pinus pseudostrobus,smooth-bark Mexican pine
Pinus pungens,Table Mountain pine
Pinus quadrifolia,Parry pinyon
Pinus radiata,Monterey pine
Pinus remota,Texas pinyon
Pinus resinosa,red pine
Pinus rigida,pitch pine
Pinus sabiniana,gray pine
Pinus serotina,pond pine
Pinus strobus,Eastern White Pine
Pinus sylvestris,Scots Pine
Pinus taeda,loblolly pine
Pinus virginiana,Torrey pine
Pithecellobium dulce,Monkeypod
Platanus x acerifolia,London plane-tree
Platanus occidentalis,American sycamore
Platanus racemosa,California sycamore
Platanus wrightii,Arizona sycamore
Plumeria rubra,frangipani
Populus alba,European white poplar
Populus angustifolia,Narowleaf cottonwood
Populus balsamifera,Balsam poplar
Populus x canadensis,Carolina poplar
Populus deltoides,eastern cottonwood
Populus fremontii,Frémont's cottonwood
Populus grandidentata,largetooth aspen
Populus nigra,Lombardy poplar
Populus heterophylla,downy poplar
Populus simonii,Simon poplar
Populus tremuloides,trembling aspen
Populus trichocarpa,black cottonwood
Prosopis glandulosa,mamey sapote
Prosopis juliflora,mesquite
Prosopis laevigata,smooth mesquite
Prosopis pubescens,screwbean mesquite
Prosopis velutina,velvet mesquite
Protium copal,copal tree
Prunus americana,American plum
Punus avium,Sweet cherry
Prunus cerasus,Sour cherry
Prunus emariginata,Bitter cherry
Prunus erythroxylon,palo prieto
Prunus maackii,Amur choke cherry
Prunus maritima,beach plum
Prunus mexicana,Mexican plum
Prunus nigra,Canada plum
Prunus pensylvanica,pin cherry
Prunus serotina,black cherry
Prunus serrulata,Japanes flowering cherry
Prunus virginiana,Choke cherry
Pseudobombax ellipticum,shaving brush tree
Pseudophoenix sargentii,Florida cherry palm
Pseudotsuga macrocarpa,bigcone Douglas-fir
Pseudotsuga menziesii,Douglas-fir
Psidium guajava,common guava
Psidium guineense,Brazilian guava
Psorothamnus spinosus,smokethorn
Ptelea crenulata,California hoptree
Ptelea trifoliata,common hoptree
Quercus acutifolia,
Quercus affinis,
Quercus alba,White Oak
Quercus arizonica,Arizona white oak
Quercus arkansana,Arkansas oak
Quercus bicolor,swamp white oak
Quercus boyntonii,Boynton oak
Quercus breviloba,shallow-lobed oak
Quercus buckleyi,Texas red oak
Quercus candicans,
Quercus castanea,
Quercus chapmanii,Chapman oak
Quercus chihuahuensis,Chihuahua oak
Quercus chrysolepis,canyon live oak
Quercus coccinea,Scarlet oak
Quercus coccolobifolia,
Quercus conspersa,
Quercus conzattii,
Quercus crassifolia,
Quercus crassipes,
Quercus deliquescens,
Quercus depressa,
Quercus diversifolia,
Quercus dysophylla,
Quercus elliptica,
Quercus ellipsoidalis,Northern pin oak
Quercus emoryi,Emory oak
Quercus frutex,
Quercus fulva,
Quercus galeanensis,
Quercus garryana,Garry oak
Quercus geminata,sand live oak
Quercus gentryi,
Quercus georgiana,Georgia oak
Quercus glabrescens,
Quercus glaucoides,
Quercus greggii,Mexican oak
Quercus grisea,gray oak
Quercus havardii,shinnery oak
Quercus hintoniorum,
Quercus hypoleucoides,silverleaf oak
Quercus hypoxantha,
Quercus ilicifolia,bear oak
Quercus laceyi,Lacey oak
Quercus laeta,
Quercus laevis,turkey oak
Quercus laurifolia,swamp laurel oak
Quercus laurina,
Quercus lobata,valley oak
Quercus lyrata,overcup oak
Quercus macrocarpa,Bur oak
Quercus magnoliifolia,encino amarillo
Quercus marilandica,blackjack oak
Quercus muehlenbergii,Chinquapin oak
Quercus mexicana,
Quercus michauxii,swamp chestnut oak
Quercus microphylla,
Quercus mohriana,Mohr oak
Quercus montana,Chestnut oak
Quercus myrtifolia,myrtle oak
Quercus nigra,water oak
Quercus oblongifolia,Arizona blue oak
Quercus obtusata,
Quercus oglethorpensis,Oglethorpe oak
Quercus oleoides,encina
Quercus palmeri,Palmer oak
Quercus palustris,pin oak
Quercus phellos,willow oak
Quercus polymorpha,Mexican white oak
Quercus potosina,
Quercus prinoides,Dwarf chinquapin oak
Quercus pungens,sandpaper oak
Quercus resinosa,
Quercus rugosa,netleaf oak
Quercus robur,English oak
Quercus rubra,Red oak
Quercus salicifolia,
Quercus sapotifolia,
Quercus scytophylla,
Quercus sinuata,Durand oak
Quercus shumardii,Shumard Oak
Quercus subspathulata,
Quercus texana,Nuttall's oak
Quercus tomentella,island oak
Quercus tuberculata,
Quercus turbinella,turbinella oak
Quercus urbanii,
Quercus vaseyana,Vasey oak
Quercus velutina,Black oak
Quercus virginiana,Southern live oak
Quercus xalapensis,xalapa oak
Quercus zempoaltepecana,
Rhamnus cathartica,European buckthorn
Rhamnus frangula,Glossy buckthorn
Rhamnus purshiana,cascara buckthorn
Rhus copallinum,shining sumac
Rhus glabra,Smooth sumac
Rhus lanceolata,prairie sumac
Rhus microphylla,littleleaf sumac
Rhus ovata,sugar sumac
Rhus typhina,Staghorn sumac
Rhus virens,evergreen sumac
Robinia neomexicana,rose locust
Robinia pseudoacacia,black locust
Robinia viscosa,clammy locust
Roupala montana,
Roystonea regia,Cuban royal palm
Sabal mauritiiformis,
Salix alba,Golden weeping willow
Salix amygdaloides,peachleaf willow
Salix arbusculoides,Littletree Willow
Salix bebbiana,Bebb's willow
Salix bonplandiana,Bonpland willow
Salix daphnoides,Violet willow
Salix discolor,pussy willow
Salix exigua,Sandbar willow
Salix floridana,Florida willow
Salix fragilis,Crack willow
Salix hookeriana,Hooker willow
Salix humboldtiana,Humboldt's willow
Salix laevigata,red willow
Salix lucida,Pacific willow
Salix nigra,black willow
Salix pellita,Satiny willow
Salix pendandra,Laural willow
Salix pyrifolia,Balsam willow
Salix purpurea,Purple-osier willow
Salix sericea,silky willow
Salix taxifolia,yew-leaf willow
Salix viminalis,Basket willow
Sambucus melanocarpa,Black-Cerry Elder
Sambucus canadensis,American elder
Sambucus pubens,Eastern Red Elderberry
Sambucus cerulea,Blue-berry Elder
Sambucus callicarpa,Red-berry Elder
Sapindus marginatus,Florida soapberry
Sapindus saponaria,wingleaf soapberry
Saurauia leucocarpa,
Saurauia oreophila,
Saurauia pustulata,
Saurauia scabrida,
Saurauia serrata,
Saurauia villosa,
Sassafras albidum,Sassafras
Sciadopitys verticillata,Umbrella-Pine
Sebastiania pavoniana,Mexican jumping bean
Senegalia berlandieri,Berlandier Acacia
Senegalia greggii,Acacia greggii
Sequoia sempervirens,coast redwood
Sequoiadendron giganteum,Sierra redwood
Sideroxylon celastrinum,saffron plum
Sideroxylon foetidissimum,false mastic
Sideroxylon lanuginosum,gum bully
Sideroxylon lycioides,buckthorn bully
Sideroxylon salicifolium,white bully
Simarouba glauca,paradise-tree
Solanum erianthum,potatotree
Sorbus americana,American mountain-ash
Sorbus decora,showy mountain-ash
Sorbus aucuparia,European mountain-ash
Sorbussitchensis,Sitka mountain-ash
Spathacanthus hahnianus,
Spondias mombin,yellow mombin
Spondias purpurea,jocote
Swietenia humilis,Pacific Coast mahogany
Swietenia macrophylla,mahogany
Swietenia mahagoni,American mahogany
Symplocos tinctoria,common sweetleaf
Syringa vulgaris,Common lilac
Tabebuia rosea,pink poui
Tapirira mexicana,
Taxodium ascendens,
Taxodium distichum,Bald-Cypress
Taxodium mucronatum,
Taxus baccata,English yew
Taxus brevifolia,western yew
Taxus canadensis,Canada yew
Taxus cuspidata,Japanese yew
Taxus floridana,Florida yew
Terminalia amazonia,
Theobroma cacao,cacao tree
Thuja occidentalis,eastern white-cedar
Thuja orientalis,Oriental-cedar
Thuja plicata,western red cedar
Toxicodendron vernix,Poison-sumac
Tilia americana,American basswood
Tilia cordata,Little-leaf linden
Tilia caroliniana,
Tilia tomentosa,White linden
Torreya californica,California torreya
Torreya taxifolia,Florida nutmeg
Tsuga canadensis,eastern hemlock
Tsuga caroliniana,Carolina hemlock
Tsuga heterophylla,western hemlock
Tsuga mertensiana,mountain hemlock
Ulmus americana,White elm
Ulmus crassifolia,Texas cedar elm
Ulmus glabra,Scotch elm
Ulmus ismaelis,
Ulmus mexicana,Mexican elm
Ulmus rubra,slippery elm
Ulmus pumila,Siberian elm
Ulmus procera,English elm
Ulmus serotina,September elm
Ulmus thomasii,rock elm
Umbellularia,Oregon myrtle
Ungnadia,Mexican buckeye
Vachellia cornigera,bullhorn acacia
Vachellia farnesiana,sweet acacia
Vachellia rigidula,blackbrush
Vauquelinia californica,Arizona rosewood
Viburnum lentago,Sweet viburnum
Viburnum opulus,European Cranberry Viburnum
Viburnum lantana,Wayfaring viburnum
Viburnum edule,Squashberry viburnum
Viburnum trilobum,Cranberry viburnum
Viburnum prunifolium,blackhaw
Washingtonia filifera,desert fan palm
Wimmeria mexicana,papelío
Yucca brevifolia,Joshua tree
Zanthoxylum americanum,common prickly-ash
Zanthoxylum clava-herculis,southern prickly ash
Zanthoxylum fagara,wild lime
Zanthoxylum flavum,yellow sandalwood
Zelkova serrata,Japanese zelkova
Ziziphus obtusifolia,lotebush
Cephalanthus occidentalis,Button-bush";

        $Data = str_getcsv($CsvString, "\n"); //parse the rows
        $result = [];
        foreach($Data as &$Row) {
            $Row = str_getcsv($Row, ","); 
            $result[] = $Row;
        }

        foreach ($result as $key => $value) {
            $data = [
                'trees_name_eng'=>$value[1],
                'trees_name_lat'=>$value[0]
            ];
            $this->db->insert('trees', $data);    
        }
    }

    public function down() {
        
    }

}
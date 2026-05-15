'use client';
import { useState, useEffect } from 'react';

// Exact type colors matching your screenshot
const typeColors: { [key: string]: string } = {
  grass: '#78C850',
  poison: '#A040A0',
  fire: '#F08030',
  water: '#6890F0',
  flying: '#A890F0',
  bug: '#A8B820',
  normal: '#A8A878',
  electric: '#F8D030',
  ground: '#E0C068',
  fairy: '#EE99AC',
  fighting: '#C03028',
  psychic: '#F85888',
  rock: '#B8A038',
  ghost: '#705898',
  ice: '#98D8D8',
  dragon: '#7038F8',
  dark: '#705848',
  steel: '#B8B8D0'
};

export default function Pokedex() {
  const [pokemons, setPokemons] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  // ✅ API CONNECTION: https://pokeapi.co/
  useEffect(() => {
    const fetchAllPokemon = async () => {
      try {
        const resList = await fetch('https://pokeapi.co/api/v2/pokemon?limit=15');
        const listData = await resList.json();

        const detailedData = await Promise.all(
          listData.results.map(async (item: any) => {
            const resDetail = await fetch(item.url);
            const detail = await resDetail.json();
            return {
              id: detail.id.toString().padStart(4, '0'),
              name: detail.name.charAt(0).toUpperCase() + detail.name.slice(1),
              image: detail.sprites.front_default,
              height: (detail.height / 10).toFixed(1),
              weight: (detail.weight / 10).toFixed(1),
              types: detail.types.map((t: any) => t.type.name)
            };
          })
        );

        setPokemons(detailedData);
        setLoading(false);
      } catch (err) {
        console.error('API Error:', err);
        setLoading(false);
      }
    };

    fetchAllPokemon();
  }, []);

  if (loading) return <div className="flex justify-center items-center h-screen text-gray-700 text-xl">Loading...</div>;

  return (
    // ✅ LIGHT BACKGROUND changed from black → light gray
    <div className="min-h-screen bg-gray-100 text-gray-900 py-10 px-6">
      
      {/* Main Title — exactly like screenshot */}
      <h1 className="text-center text-[clamp(1.8rem,3vw,2.5rem)] font-bold mb-12">
        Pokedex API Next.js Laboratory
      </h1>

      {/* 5 columns grid, exact spacing */}
      <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 max-w-[1200px] mx-auto">
        
        {pokemons.map((pkm) => (
          
          <div 
            key={pkm.id} 
            className="bg-white rounded-2xl p-5 text-center shadow-md border border-gray-200"
          >
            
            {/* ID — top left */}
            <p className="text-sm text-gray-500 text-left m-0">{pkm.id}</p>

            {/* ✅ LARGE IMAGE — perfect size same as your example */}
            <img 
              src={pkm.image} 
              alt={pkm.name} 
              className="w-[140px] h-[140px] mx-auto my-2 object-contain"
            />

            {/* Name — gray text, uppercase style */}
            <h3 className="text-gray-500 font-medium text-lg mb-3">
              {pkm.name.toUpperCase()}
            </h3>

            {/* Type badges — exact colors, shape & spacing */}
            <div className="flex justify-center gap-2 mb-4">
              {pkm.types.map((type: string) => (
                <span 
                  key={type}
                  style={{ backgroundColor: typeColors[type] }}
                  className="text-white text-[11px] font-semibold px-3 py-1 rounded-full uppercase"
                >
                  {type}
                </span>
              ))}
            </div>

            {/* Height & Weight — exact symbols and layout */}
            <p className="text-xs text-gray-600">
              ↥ {pkm.height} m &nbsp; ⚖ {pkm.weight} kg
            </p>

          </div>
        ))}

      </div>
    </div>
  );
}